BASE_DIR := $(shell pwd)

.PHONY: clean
clean:
	rm -rf $(BASE_DIR)/vendor

.PHONY: install
install:
	composer install

.PHONY: lint
lint:
	composer lint

.PHONY: analyse
analyse:
	composer analyse

.PHONY: serve
serve:
	composer serve
