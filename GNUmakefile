PHP_VERSIONS := latest min old
PHP_DOCKERFILES := $(patsubst %,docker/Dockerfile-%,$(PHP_VERSIONS))
LANGUAGES := nl_NL es_ES
MO_FILES := $(patsubst %,common/languages/webwinkelkeur-%.mo,$(LANGUAGES))

all : docker webwinkelkeur/readme.txt trustprofile/readme.txt $(MO_FILES)
.PHONY : all

docker : $(PHP_DOCKERFILES)
.PHONY : docker

docker/Dockerfile-% : docker/Dockerfile.php
	php $< $* > $@~
	mv $@~ $@

%/readme.txt : %/project.yml bin/gen_readme changelog.md
	./bin/gen_readme $* > $@~
	mv $@~ $@

common/languages/%.po : common/languages/webwinkelkeur.pot
	msgmerge -U $@ $<
	touch $@

%.mo : %.po
	msgfmt $< -o $@