<div id="ct-dialog-window" class="ct-dialog-window" 
	ng-if="dialogWindow" 
	ng-class="{'ct-add-form-dialog':dialogForms['showAddItemDialogForm']}"
	ng-click="hideDialogWindow()">
	<div class="ct-dialog-window-content-wrap"
		ng-click="$event.stopPropagation()">

		<?php do_action("ct_dialog_window"); ?>
		
		<div class="ct-close-dialog ct-action-button" ng-click="hideDialogWindow()"><i class="fa fa-times fa-lg"></i></div>
	</div>
</div><!-- #ct-dialog-window -->

<!-- Global Color Dialog Window -->
<div class="oxygen-global-colors-new-color-bg"
    ng-show="addNewColorDialog"
    ng-click="hideAddNewColorDialog()">
</div>
<div id="oxygen-global-colors-new-color-dialog" class="oxygen-global-colors-new-color oxygen-global-colors-new-color-dialog"
    ng-show="addNewColorDialog">
    <h2><?php _e("New Color","oxygen"); ?></h2>
    <div class="oxygen-input">
        <div class="oxygen-global-color"
            ng-style="{backgroundColor:addNewColorDialogValue}"></div>
        <input type="text" spellcheck="false" placeholder="<?php _e("Color Name","oxygen"); ?>"
            ng-model="newGlobalSettingsColorName" ng-model-options="{ debounce: 10 }">
    </div>
    <div class="oxygen-select oxygen-select-box-wrapper"
        ng-click="toggleOxygenSelectBox($event)">
        <div class="oxygen-select-box">
            <div class="oxygen-select-box-current ng-binding">{{iframeScope.getGlobalColorSet(colorSetIDToAdd).name}}</div>
            <div class="oxygen-select-box-dropdown"></div>
        </div>
        <div class="oxygen-select-box-options">
            <div class="oxygen-select-box-option"
                ng-repeat="(key,set) in iframeScope.globalColorSets.sets"
                ng-click="$parent.colorSetIDToAdd=set.id">{{set.name}}</div>
        </div>
    </div>
    <div class="oxygen-add-global-color-button"
     ng-click="iframeScope.addNewColor(newGlobalSettingsColorName, colorSetIDToAdd, 'latest')">add</div>
</div>
<!-- /Global Color Dialog Window -->
