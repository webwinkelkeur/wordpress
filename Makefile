all : webwinkelkeur/readme.txt

%/readme.txt : %/project.yml bin/gen_readme changelog.md
	./bin/gen_readme $* > $@~
	mv $@~ $@
