#!/usr/bin/env bash
set -Eeuo pipefail

REMOTE="${REMOTE:-origin}"
MAIN_BRANCH="${MAIN_BRANCH:-main}"
DEV_BRANCH="${DEV_BRANCH:-dev-main}"

say() { echo -e "$*"; }
die() { echo -e "❌ $*" >&2; exit 1; }

# 0) Reponi tekshirish
git rev-parse --is-inside-work-tree >/dev/null 2>&1 || die "Git repo emas."

# 1) Faqat dev-main’dan ishlatish (auto-switch va auto-stash)
START_BRANCH="$(git rev-parse --abbrev-ref HEAD || true)"
NEED_STASH=0
if ! git diff --quiet || ! git diff --cached --quiet; then
  STASH_MSG="release.sh-auto-stash-$(date +%s)"
  say "🧰 Lokal o'zgarishlar topildi — stash qilinadi: $STASH_MSG"
  git stash push -u -m "$STASH_MSG" >/dev/null || true
  NEED_STASH=1
fi

if [[ "$START_BRANCH" != "$DEV_BRANCH" ]]; then
  say "ℹ️ $DEV_BRANCH ga o'tilmoqda..."
  git checkout "$DEV_BRANCH" >/dev/null 2>&1 || die "$DEV_BRANCH branch topilmadi."
fi

# 2) Remote va teglar
say "🔄 Remote va taglar olinmoqda..."
git fetch --prune "$REMOTE" >/dev/null 2>&1 || true
git fetch --tags "$REMOTE" >/dev/null 2>&1 || true

# 3) dev-main’ni yangilash (rebase to clean history)
say "⬇️  $DEV_BRANCH yangilanmoqda..."
git pull --rebase "$REMOTE" "$DEV_BRANCH"

# 4) So‘nggi tagni topish (semver vX.Y.Z)
last_tag="$(git tag -l 'v*' --sort=-v:refname | head -n1 || true)"
if [[ -z "${last_tag}" ]]; then last_tag="v0.0.0"; fi
say "🏷️  So'nggi tag: ${last_tag}"

# 5) Release turini so'rash
say "🔍 Yangi release turi:"
say "  1) Bugfix (patch)  — ${last_tag} → v?.?.(Z+1)"
say "  2) Feature (minor) — ${last_tag} → v?.(Y+1).0"
say "  3) Breaking (major) — ${last_tag} → v(X+1).0.0"
read -rp "🧩 Tanlang (1/2/3): " choice
[[ "$choice" =~ ^[123]$ ]] || die "Noto'g'ri tanlov. 1, 2 yoki 3."

# 6) Versiyani hisoblash + bot-bot band bo'lsa keyingisini tanlash
ver="${last_tag#v}"
IFS='.' read -r major minor patch <<<"$ver"

bump() {
  local t="$1"
  local M=$major m=$minor p=$patch
  case "$t" in
    1) p=$((p+1)) ;;
    2) m=$((m+1)); p=0 ;;
    3) M=$((M+1)); m=0; p=0 ;;
  esac
  echo "v${M}.${m}.${p}"
}

# topilmagan tagga qadar increment qilamiz
new_tag="$(bump "$choice")"
# Agar bor bo'lsa, keyingisiga o'tamiz (tanlov bo'yicha)
while git rev-parse -q --verify "refs/tags/${new_tag}" >/dev/null; do
  case "$choice" in
    1) patch=$((patch+1)) ;;
    2) minor=$((minor+1)); patch=0 ;;
    3) major=$((major+1)); minor=0; patch=0 ;;
  esac
  new_tag="v${major}.${minor}.${patch}"
done

say "📦 Yangi versiya: ${new_tag}"

# 7) main ga merge
say "🚀 ${DEV_BRANCH} → ${MAIN_BRANCH} merge jarayoni..."
git checkout "$MAIN_BRANCH"
# mainni yangilab olamiz
git pull --rebase "$REMOTE" "$MAIN_BRANCH" || true

# Avval to'g'ridan merge urinamiz
if git merge --no-edit "$DEV_BRANCH"; then
  say "✅ Merge muvaffaqiyatli."
else
  say "⚠️ Merge konflikt. Rebase yo'li bilan to'g'rilanadi..."
  git merge --abort || true
  git checkout "$DEV_BRANCH"
  # dev-main ni main ustiga rebase
  if git rebase "$MAIN_BRANCH"; then
    git checkout "$MAIN_BRANCH"
    git merge --ff-only "$DEV_BRANCH" || die "FF merge muvaffaqiyatsiz. Qo'lda hal qiling."
    say "✅ Rebase + FF merge ok."
  else
    say "❌ Rebase vaqtida konflikt. Rebase abort qilinmoqda."
    git rebase --abort || true
    die "Konfliktlarni qo'lda hal qiling va keyin qayta ishga tushiring."
  fi
fi

# 8) Main push (agar non-ff bo'lsa, rebase qilib yana urinadi)
say "📤 ${MAIN_BRANCH} push qilinmoqda..."
if ! git push "$REMOTE" "$MAIN_BRANCH"; then
  say "↩️  Upstream o'zgargan. Rebase va qayta push..."
  git pull --rebase "$REMOTE" "$MAIN_BRANCH"
  git push "$REMOTE" "$MAIN_BRANCH"
fi

# 9) Tag yaratish va push
if git rev-parse -q --verify "refs/tags/${new_tag}" >/dev/null; then
  say "ℹ️ Tag allaqachon bor: ${new_tag} (push o‘tkazib yuborildi)."
else
  say "🏷️  Tag yaratilmoqda: ${new_tag}"
  git tag -a "${new_tag}" -m "Release ${new_tag}"
  git push "$REMOTE" "refs/tags/${new_tag}:refs/tags/${new_tag}"
fi

# 10) dev-main’ga qaytib, mainni sync qilib qo'yamiz
git checkout "$DEV_BRANCH"
# dev’ni origin bilan sync
git pull --rebase "$REMOTE" "$DEV_BRANCH" || true
# mainni devga fast-forward qilib qo'yamiz (agar zarur bo'lsa)
git merge --ff-only "$MAIN_BRANCH" || true

# 11) Stashni tiklash va boshlang'ich branchga qaytish (agar dev-main emas bo'lsa)
if [[ "$START_BRANCH" != "$DEV_BRANCH" ]]; then
  git checkout "$START_BRANCH" || true
fi
if [[ "$NEED_STASH" -eq 1 ]]; then
  say "🧰 Stash tiklanmoqda..."
  git stash pop || true
fi

say "✅ Release ${new_tag} tayyor!  (${DEV_BRANCH} ↔ ${MAIN_BRANCH} sync yakunlandi)"
