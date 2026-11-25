PHP_VERSIONS := latest min old
PHP_DOCKERFILES := $(patsubst %,docker/Dockerfile-%,$(PHP_VERSIONS))
LANGUAGES := nl_NL es_ES de_DE
MO_FILES := $(patsubst %,common/languages/webwinkelkeur-%.mo,$(LANGUAGES))

all : docker webwinkelkeur/readme.txt trustprofile/readme.txt $(MO_FILES)
.PHONY : all

docker : $(PHP_DOCKERFILES)
.PHONY : docker

# Run the tests on the oldest and newest supported PHP versions.
TEST_PHP_VERSIONS := 7.4 8.4

test : $(patsubst %,test-php%,$(TEST_PHP_VERSIONS))
.PHONY : test

test-php% :
	PHP_VERSION=$* tests/run.sh
.PHONY : test-php%

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