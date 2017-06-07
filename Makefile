all : webwinkelkeur/readme.txt evalor/readme.txt

%/readme.txt : %/project.yml
	./bin/gen_readme $* > $@~
	mv $@~ $@
