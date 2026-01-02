# Makefile (developer convenience)
#
# Creates a WordPress-installable plugin ZIP:
# the archive root contains the plugin folder (not just its contents).

.PHONY: help build clean bump-patch

PLUGIN_NAME := guild-mythic-plus-raiderio
BUILD_DIR := $(CURDIR)/build
STAGING_DIR := $(CURDIR)/dist/staging
STAGING_PLUGIN_DIR := $(STAGING_DIR)/$(PLUGIN_NAME)
ZIP_NAME := $(PLUGIN_NAME).zip
ZIP_PATH := $(BUILD_DIR)/$(ZIP_NAME)
PLUGIN_FILE := guild-mythic-plus-raiderio.php

# Extract current version from plugin file
CURRENT_VERSION := $(shell grep -E "^\s*\*\s*Version:" $(PLUGIN_FILE) | sed -E 's/.*Version:\s*([0-9]+\.[0-9]+\.[0-9]+).*/\1/')

help:
	@echo "Targets:"
	@echo "  make build       - Build WordPress plugin ZIP (creates temporary staging, then cleans up)"
	@echo "  make clean       - Remove build artifacts"

build:
	@echo "Building WordPress plugin ZIP..."
	@mkdir -p "$(BUILD_DIR)"
	@rm -f "$(ZIP_PATH)"
	@echo "Compiling translation files..."
	@for po_file in languages/*.po; do \
		mo_file=$${po_file%.po}.mo; \
		echo "  Compiling $$po_file -> $$mo_file"; \
		msgfmt -o "$$mo_file" "$$po_file"; \
	done
	@echo "Creating temporary staging directory..."
	@rm -rf "$(STAGING_DIR)"
	@mkdir -p "$(STAGING_PLUGIN_DIR)/includes"
	@mkdir -p "$(STAGING_PLUGIN_DIR)/assets"
	@mkdir -p "$(STAGING_PLUGIN_DIR)/languages"
	@echo "Copying source files to staging..."
	@cp -r includes/* "$(STAGING_PLUGIN_DIR)/includes/"
	@cp -r assets/* "$(STAGING_PLUGIN_DIR)/assets/"
	@cp -r languages/* "$(STAGING_PLUGIN_DIR)/languages/"
	@cp guild-mythic-plus-raiderio.php "$(STAGING_PLUGIN_DIR)/"
	@cp README.md "$(STAGING_PLUGIN_DIR)/"
	@cp LICENSE "$(STAGING_PLUGIN_DIR)/"
	@echo "Creating ZIP archive..."
	@cd "$(STAGING_DIR)" && zip -r "$(ZIP_PATH)" "$(PLUGIN_NAME)" \
		-x "$(PLUGIN_NAME)/**/*.log" \
		-x "$(PLUGIN_NAME)/**/.DS_Store"
	@echo "Cleaning up temporary staging directory..."
	@rm -rf "$(STAGING_DIR)"
	@echo "✓ Build complete: $(ZIP_PATH)"

clean:
	@echo "Cleaning build artifacts..."
	@rm -rf "$(BUILD_DIR)"
	@rm -rf "$(STAGING_DIR)"
	@echo "✓ Clean complete"

