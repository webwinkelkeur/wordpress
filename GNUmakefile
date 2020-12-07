PHP_VERSIONS := latest min old
PHP_DOCKERFILES := $(patsubst %,docker/Dockerfile-%,$(PHP_VERSIONS))

all : docker webwinkelkeur/readme.txt
.PHONY : all

docker : $(PHP_DOCKERFILES)
.PHONY : docker

docker/Dockerfile-% : docker/Dockerfile.php
	php $< $* > $@~
	mv $@~ $@

%/readme.txt : %/project.yml bin/gen_readme changelog.md
	./bin/gen_readme $* > $@~
	mv $@~ $@
