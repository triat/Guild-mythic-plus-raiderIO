# Makefile (developer convenience)
#
# Creates a WordPress-installable plugin ZIP:
# the archive root contains the plugin folder (not just its contents).

.PHONY: help zip clean sync

PLUGIN_NAME := guild-mythic-plus-raiderio
BUILD_DIR := $(CURDIR)/build
STAGING_DIR := $(CURDIR)/dist/staging
STAGING_PLUGIN_DIR := $(STAGING_DIR)/$(PLUGIN_NAME)
ZIP_NAME := $(PLUGIN_NAME).zip
ZIP_PATH := $(BUILD_DIR)/$(ZIP_NAME)

help:
	@echo "Targets:"
	@echo "  make sync   - Sync source files to staging (clean + copy)"
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

sync:
	@echo "Syncing source files to $(STAGING_PLUGIN_DIR)"
	@rm -rf "$(STAGING_PLUGIN_DIR)"
	@mkdir -p "$(STAGING_PLUGIN_DIR)/includes"
	@mkdir -p "$(STAGING_PLUGIN_DIR)/assets"
	@cp -r includes/* "$(STAGING_PLUGIN_DIR)/includes/"
	@cp -r assets/* "$(STAGING_PLUGIN_DIR)/assets/"
	@cp guild-mythic-plus-raiderio.php "$(STAGING_PLUGIN_DIR)/"
	@cp README.md "$(STAGING_PLUGIN_DIR)/"
	@cp LICENSE "$(STAGING_PLUGIN_DIR)/"
	@echo "✓ Source files synced to staging"

