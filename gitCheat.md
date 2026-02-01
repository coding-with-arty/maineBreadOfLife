# Git Cheat Sheet for Web Development Teams

## 🟦 Setup & Basics

### Check current repo status

git status

### See commit history (compact)

git log --oneline --graph --decorate

### Stage all changes

git add .

### Stage a specific file

git add path/to/file

## 🟩 Committing Work

### Commit staged changes with a message

git commit -m "Describe what you changed"

### Commit all tracked changes (skip staging)

git commit -am "Quick commit message"

## 🟧 Syncing With the Team (Fetch, Pull, Push)

### Download new commits from origin without merging

git fetch

### Fetch + merge remote changes into your branch

git pull

### Push your local commits to the remote branch

git push

### Push a new branch to the remote

git push -u origin branch-name

## 🟪 Branching (Team Workflow)

### Create a new branch

git branch feature/my-task

### Switch to a branch

git checkout feature/my-task

### Create AND switch to a new branch

git checkout -b feature/my-task

### List all branches

git branch -a

## 🟥 Merging & Reviewing

### Merge another branch into your current one

git merge branch-name

### Abort a merge if things go wrong

git merge --abort

## 🟨 Undoing Mistakes (Safe Resets)

### Unstage a file (keep changes)

git reset HEAD path/to/file

### Undo last commit but keep changes staged

git reset --soft HEAD~1

### Undo last commit and unstage changes (keep working copy)

git reset --mixed HEAD~1

### Hard reset to last commit (dangerous: discards changes)

git reset --hard HEAD~1

## 🟫 Stashing (Pause Work Without Committing)

### Stash your uncommitted changes

git stash

### Apply the most recent stash

git stash apply

### List stashes

git stash list

## 🟩 Useful Extras

### See what changed in a file

git diff path/to/file

### Remove a tracked file from Git but keep it locally

git rm --cached filename

### Clone a repo

git clone <repo-url>
