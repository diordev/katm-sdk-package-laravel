#!/bin/bash

# USAGE: ./release.sh v1.2.3

set -e  # xatolik bo'lsa, script to'xtaydi

# 1. Tekshir: versiya raqami berilganmi
if [ -z "$1" ]; then
  echo "❌ Versiya raqami kerak. Masalan: ./release.sh v1.0.0"
  exit 1
fi

VERSION=$1

# 2. Ishlab chiqish branchidan chiqamiz
echo "🚀 Switching to dev-main branch..."
git checkout dev-main

# 3. Eng so‘nggi o‘zgarishlar
git pull origin dev-main

# 4. Mainga o‘tamiz
echo "🚀 Merging dev-main -> main"
git checkout main
git pull origin main
git merge dev-main

# 5. Tag qo‘yish
echo "🏷️ Creating tag: $VERSION"
git tag -a $VERSION -m "Release $VERSION"

# 6. Git push (main + tag)
echo "📤 Pushing to origin..."
git push origin main
git push origin $VERSION

echo "✅ Release $VERSION created and pushed successfully!"
