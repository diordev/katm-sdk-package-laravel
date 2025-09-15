#!/bin/bash

set -e

echo "🔍 Yangi release chiqarish turi:"
echo "1) Bugfix (patch) - v1.0.0 → v1.0.1"
echo "2) Feature (minor) - v1.0.0 → v1.1.0"
echo "3) Breaking (major) - v1.0.0 → v2.0.0"
read -p "🧩 Tanlang (1/2/3): " type

if [[ "$type" != "1" && "$type" != "2" && "$type" != "3" ]]; then
    echo "❌ Noto‘g‘ri tanlov. 1, 2 yoki 3 ni tanlang."
    exit 1
fi

# Eng so‘nggi tagni olish
last_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")

# Versiyani bo‘lish
version=${last_tag#v}
IFS='.' read -r major minor patch <<< "$version"

case "$type" in
    1) patch=$((patch + 1)) ;;
    2) minor=$((minor + 1)); patch=0 ;;
    3) major=$((major + 1)); minor=0; patch=0 ;;
esac

new_version="v$major.$minor.$patch"

echo "📦 Yangi versiya: $new_version"

# dev-main ni main ga merge qilish
echo "🚀 Switching to dev-main branch..."
git checkout dev-main
git pull origin dev-main

echo "🚀 Merging dev-main → main"
git checkout main
git pull origin main
git merge --no-edit dev-main

# Agar merge conflict bo'lsa toxtaydi
if [[ $? -ne 0 ]]; then
    echo "⚠️ Merge konflikt. Avval hal qiling."
    exit 1
fi

# Yangi tag yaratish
echo "📤 Pushing to origin..."
git tag -a "$new_version" -m "Release $new_version"
git push origin main
git push origin "$new_version"

echo "✅ Release $new_version tayyor va GitHub'ga push qilindi!"
