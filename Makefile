# Makefile (developer convenience)
#
# Creates a WordPress-installable plugin ZIP:
# the archive root contains the plugin folder (not just its contents).

.PHONY: help zip clean

PLUGIN_NAME := guild-mythic-plus-raiderio
BUILD_DIR := $(CURDIR)/build
STAGING_DIR := $(CURDIR)/dist/staging
ZIP_NAME := $(PLUGIN_NAME).zip
ZIP_PATH := $(BUILD_DIR)/$(ZIP_NAME)

help:
	@echo "Targets:"
	@echo "  make zip    - Build a WordPress-installable ZIP at $(ZIP_PATH)"
	@echo "  make clean  - Remove build artifacts"

zip:
	@mkdir -p "$(BUILD_DIR)"
	@rm -f "$(ZIP_PATH)"
	@echo "Building $(ZIP_PATH) from $(STAGING_DIR)/$(PLUGIN_NAME)"
	@cd "$(STAGING_DIR)" && zip -r "$(ZIP_PATH)" "$(PLUGIN_NAME)" \
		-x "$(PLUGIN_NAME)/**/*.log" \
		-x "$(PLUGIN_NAME)/**/.DS_Store"
	@echo "Done: $(ZIP_PATH)"

clean:
	@rm -rf "$(BUILD_DIR)"

