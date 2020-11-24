PHP_VERSIONS := latest min old
PHP_DOCKERFILES := $(patsubst %,docker/Dockerfile-%,$(PHP_VERSIONS))

all : docker
.PHONY : all

docker : $(PHP_DOCKERFILES)
.PHONY : docker

docker/Dockerfile-% : docker/Dockerfile.php
	php $< $* > $@~
	mv $@~ $@
