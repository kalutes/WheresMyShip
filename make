all: git-commit

git-commit:
	git add *.* make >> .local.git.out
	git commit -m 'commit' make  >> .local.git.out
	git push

