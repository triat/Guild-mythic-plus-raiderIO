# Makefile (developer convenience)
#
# Creates a WordPress-installable plugin ZIP:
# the archive root contains the plugin folder (not just its contents).

.PHONY: help zip clean

PLUGIN_SLUG := $(notdir $(CURDIR))
BUILD_DIR := $(CURDIR)/build
ZIP_NAME := $(PLUGIN_SLUG)-install.zip
ZIP_PATH := $(BUILD_DIR)/$(ZIP_NAME)

help:
	@echo "Targets:"
	@echo "  make zip    - Build a WordPress-installable ZIP at $(ZIP_PATH)"
	@echo "  make clean  - Remove build artifacts"

zip:
	@mkdir -p "$(BUILD_DIR)"
	@rm -f "$(ZIP_PATH)"
	@echo "Building $(ZIP_PATH)"
	@cd .. && zip -r "$(ZIP_PATH)" "$(PLUGIN_SLUG)" \
		-x "$(PLUGIN_SLUG)/build/*" \
		-x "$(PLUGIN_SLUG)/.git/*" \
		-x "$(PLUGIN_SLUG)/openspec/*" \
		-x "$(PLUGIN_SLUG)/dist/*" \
		-x "$(PLUGIN_SLUG)/node_modules/*" \
		-x "$(PLUGIN_SLUG)/.vscode/*" \
		-x "$(PLUGIN_SLUG)/.idea/*" \
		-x "$(PLUGIN_SLUG)/**/*.log" \
		-x "$(PLUGIN_SLUG)/**/.DS_Store"
	@echo "Done: $(ZIP_PATH)"

clean:
	@rm -rf "$(BUILD_DIR)"

