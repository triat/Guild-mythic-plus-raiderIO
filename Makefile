# Makefile (developer convenience)
#
# Creates a WordPress-installable plugin ZIP:
# the archive root contains the plugin folder (not just its contents).

.PHONY: help build clean

PLUGIN_NAME := guild-mythic-plus-raiderio
BUILD_DIR := $(CURDIR)/build
STAGING_DIR := $(CURDIR)/dist/staging
STAGING_PLUGIN_DIR := $(STAGING_DIR)/$(PLUGIN_NAME)
ZIP_NAME := $(PLUGIN_NAME).zip
ZIP_PATH := $(BUILD_DIR)/$(ZIP_NAME)

help:
	@echo "Targets:"
	@echo "  make build  - Build WordPress plugin ZIP (creates temporary staging, then cleans up)"
	@echo "  make clean  - Remove build artifacts"

build:
	@echo "Building WordPress plugin ZIP..."
	@mkdir -p "$(BUILD_DIR)"
	@rm -f "$(ZIP_PATH)"
	@echo "Creating temporary staging directory..."
	@rm -rf "$(STAGING_DIR)"
	@mkdir -p "$(STAGING_PLUGIN_DIR)/includes"
	@mkdir -p "$(STAGING_PLUGIN_DIR)/assets"
	@echo "Copying source files to staging..."
	@cp -r includes/* "$(STAGING_PLUGIN_DIR)/includes/"
	@cp -r assets/* "$(STAGING_PLUGIN_DIR)/assets/"
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

