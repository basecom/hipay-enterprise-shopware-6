#!/bin/bash

TEMPLATE_URL="git@gitlab.com:basecom-gmbh/shopware/v6/customer-projects/templates/bscshopwarelaunchpadultimate/launchpad-4.git"

fork() {
    git remote add upstream $TEMPLATE_URL
    git remote rm origin
    read -p "Please provide the target repository URL: " URL
    git remote add origin $URL
}

echo "Please choose the hoster you'd like to use:"
echo "(1) Platform.sh"
echo "(2) Docker/AWS"
echo "(3) Maxcluster"

read option

case $option in
    1)
        echo "You chose Platform.sh - Merging PSH branch"
        git fetch --all
        git merge -X theirs origin/PSH --allow-unrelated-histories --no-edit
        fork
        echo "You can initialize your remote repository now. Use: git push origin main"
        ;;
    2)
        echo "You chose AWS - Merging AWS branch"
        git fetch --all
        git merge -X theirs origin/AWS --allow-unrelated-histories --no-edit
        fork
        echo "You can initialize your remote repository now. Use: git push origin main"
        ;;
    3)
        echo "You chose Maxcluster - Merging Maxcluster branch"
        git fetch --all
        git merge -X theirs origin/Maxcluster --allow-unrelated-histories --no-edit
        fork
        echo "You can initialize your remote repository now. Use: git push origin main"
        ;;
    *)
        echo "Invalid input"
        ;;
esac
