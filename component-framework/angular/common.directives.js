var CTCommonDirectives = angular.module('CTCommonDirectives', []);

CTCommonDirectives.factory('ctScopeService', function() {
    var mem = {};
    return {
        store: function(key, val) {
            mem[key] = val;
        },
        get: function(key) {
            return mem[key];
        }
    }
});

CTCommonDirectives.factory('ctOxyCache', function() {
    var mem = {};
    return {
        store: function(key, val) {
            mem[key] = val;
        },
        get: function(key) {
            return mem[key];
        }
    }
});

CTCommonDirectives.directive("ctiriscolorpicker", function() {
    return {
        restrict: "A",
        require: "ngModel",
        scope: {
            ctiriscallback: '=',
            gradientindex: '='
        },
        
        link: function(scope, element, attrs, ngModel) {
            var debounceChange = false;
            setTimeout(function(){
                element.alphaColorPicker({
                    color: scope.$parent.iframeScope.getGlobalColorValue(ngModel.$modelValue),
                    change: function(ui) {
                        if(element.val().length == ui.color.toString().length || element.val().length === 0) {
                            if(!debounceChange) {
                                debounceChange = setTimeout(function() {
                                    ngModel.$setViewValue(scope.$parent.iframeScope.getGlobalColorValue(element.ctColorPicker('color')));
                                    clearTimeout(debounceChange);
                                    debounceChange = false;
                                }, 100);
                            }
                        }
                        if(scope.ctiriscallback) {
                            scope.ctiriscallback();
                        }
                    }
                });

                var modelString = attrs.ngModel;

                if(typeof(scope.gradientindex) !== 'undefined') {
                    modelString = modelString.replace('$index', scope.gradientindex);
                }

                scope.$parent.$watch(modelString, function( newVal ) {

                    var niceName = scope.$parent.iframeScope.getGlobalColorNiceName(newVal),
                        colorPicker = element.closest('.oxygen-color-picker');

                    jQuery('.oxy-global-color-label', colorPicker).remove();

                    if (niceName) {
                        colorPicker.removeClass('oxy-not-global-color-value').children('input').prop( "disabled", true )
                            .after("<span class='oxy-global-color-label' title='"+niceName+"'>"+niceName+"<span class='oxy-global-color-label-remove'>x</span></span>")
                    }
                    else {
                        colorPicker.addClass('oxy-not-global-color-value').children('input').prop( "disabled", false );
                        scope.$parent.$parent.activeGlobalColor = {};
                    }

                    colorPicker.addClass('oxy-not-empty-color-value');
                    if ((!newVal || newVal === "") && !ngModel.$modelValue ) {
                        colorPicker.removeClass('oxy-not-empty-color-value');
                        // unset background color
                        element.closest('.wp-picker-container').find('.wp-color-result').css("background-color","");
                        return;
                    }
                    

                    

                    element.ctColorPicker('color', scope.$parent.iframeScope.getGlobalColorValue(newVal));
                });
                
                scope.$apply();
                
            }, 0);

        }
    }
});

CTCommonDirectives.directive("ctdynamicdata", function($compile, ctScopeService) {
    return {
        restrict: "A",
        replace: true,
        scope: {
            data: "=",
            callback: "=",
        },
        link: function(scope, element, attrs) {
            
            angular.element('body').on('click', '.oxy-dynamicdata-popup-background', function() {
                angular.element('#ctdynamicdata-popup').remove();
                angular.element('.oxy-dynamicdata-popup-background').remove();
            });

            scope.dynamicDataModel = {};
            scope.showOptionsPanel = { item: false };
            scope.processCallback = function(item, dataitem, showOptions) {
                if(showOptions /*&& dataitem.properties && dataitem.properties.length > 0*/) {
                   scope.showOptionsPanel.item = item.name+item.data+dataitem.data;
                   if(item.type == "button") {
                       if (typeof scope.dynamicDataModel['settings_path'] === 'undefined') scope.dynamicDataModel['settings_path'] = item.data;
                       else scope.dynamicDataModel['settings_path'] = scope.dynamicDataModel['settings_path'] + "/" + item.data;
                   }

                }

                if(scope.callback && (!item.properties || item.properties.length == 0 )) {
                    
                    var shortcode = '[oxygen data="'+dataitem.data+'"';
                    
                    var finalVals = {};

                    var checkProperties = function(property){
                        if(scope.dynamicDataModel.hasOwnProperty(property.data) && scope.dynamicDataModel[property.data].trim !== undefined &&
                            scope.dynamicDataModel[property.data].trim()!=='' &&
                            !property.helper && scope.dynamicDataModel[property.data] !== property.nullVal && scope.fieldIsVisible( property )) {
                            finalVals[property.data] = scope.dynamicDataModel[property.data];
                        }
                        _.each(property.properties, function(property) {
                            checkProperties( property );
                        });
                    };

                    _.each(dataitem.properties, function(property) {
                        checkProperties( property );
                    });

                    _.each(finalVals, function(property, key) {
                        property = property.replace(/'/g, "__SINGLE_QUOTE__");
                        shortcode+=' '+key+'="'+property+'"';
                    })

                    if(dataitem['append']) {
                        shortcode+=' '+dataitem['append'];
                    }

                    if (typeof scope.dynamicDataModel['settings_path'] !== 'undefined') {
                        shortcode+=' settings_path="'+scope.dynamicDataModel['settings_path']+'"';
                    }

                    shortcode+=']';

                    scope.callback(shortcode);
                    angular.element('#ctdynamicdata-popup').remove();
                    angular.element('.oxy-dynamicdata-popup-background').remove();
                }
                //scope.dynamicDataModel={};
            }

            scope.applyChange = function(property) {
                if(property.change) {
                    eval(property.change);
                }
            }

            /*
            * Get the user back to the root panel
            * */
            scope.back = function( localScope ) {
                scope.dynamicDataModel={};
                scope.showOptionsPanel.item = false;
            }

            /*
            * Determines if a field should be visible by evaluating the dynamic condition, if set
            * */
            scope.fieldIsVisible = function( item ) {
                if( typeof item.show_condition === 'undefined' ) return true;
                return scope.$eval( item.show_condition );
            }

            /*
            * Recursive function that determines if a child panel is visible, in order to make the parent one visible too
            */
            scope.isChildPanelVisible = function( item, dataitem ) {
                if( !scope.showOptionsPanel.item ) return false;
                if( item.properties ) {
                    var result = false;
                    for( var i = item.properties.length -1; i >=0; i--) {
                        if( scope.showOptionsPanel.item === item.properties[i].name + item.properties[i].data + dataitem.data ) {
                            return true;
                        } else if( item.properties[i].properties ) {
                            result = scope.isChildPanelVisible( item.properties[ i ], dataitem );
                            if(result) return true;
                        }
                    }
                    return result;
                } else return false;
            }

            /*
            * Determines if the current panel is a navigation-only panel, to know if we should make the "INSERT" button visible or not
            */
            scope.isNavigationOnlyPanel = function( item ) {
                var result = true;
                for( var i = item.properties.length -1; i >= 0; i-- ){
                    if( item.properties[i].type != 'button' && item.properties[i].type != "heading" && item.properties[i].type != "label" ){
                        result = false;
                        break;
                    }
                }
                return result;
            };

            element.on('click', function() {
                scope.showOptionsPanel.item = false;
                scope.dynamicDataModel={};
                angular.element('body #ctdynamicdata-popup').remove();
                angular.element('body .oxy-dynamicdata-popup-background').remove();
                
                var template = '<div class="oxy-dynamicdata-popup-background"></div>'+
                        '<div id="ctdynamicdata-popup" class="oxygen-data-dialog">'+
                            '<h1>Insert Dynamic Data</h1>'+
                            '<div>'+
                                '<div class="oxygen-data-dialog-data-picker"'+
                                    'ng-repeat="item in data">'+
                                    '<h2>{{item.name}}</h2>'+
                                    '<ul>'+
                                        '<li ng-repeat="dataitem in item.children" ng-mouseup="processCallback(dataitem, dataitem, true); $event.stopPropagation();">'+
                                            '<span>{{dataitem.name}}</span>'+
                                            '<div ng-if="dataitem.properties" ng-show="showOptionsPanel.item === dataitem.name+dataitem.data+dataitem.data || isChildPanelVisible( dataitem, dataitem )" class="oxygen-data-dialog-options" ng-mouseup="$event.stopPropagation();">'+
                                                '<h1>{{dataitem.name}} Options</h1>'+
                                                '<div>'+
                                                    '<div class="oxygen-data-back-button" ng-mouseup="back()">&lt; BACK</div>'+
                                                    '<div ng-repeat="property in dataitem.properties" ng-class="{inline: property.type==\'button\'}">'+
                                                        '<div  ng-include="\'dynamicDataRecursiveDialog\'" ng-class="{inline: property.type==\'button\'}"></div>'+
                                                    '</div>'+
                                                '</div>'+
                                                '<div class="oxygen-apply-button" ng-mouseup="processCallback(item, dataitem)" ng-show="!isNavigationOnlyPanel(dataitem)">INSERT</div>'+
                                            '</div>'+
                                        '</li>'+
                                    '</ul>'+
                                '</div>'+
                            '</div>'+
                        '</div>';

                var compiledElement = $compile(template)(scope);

                scope.$parent.$parent.oxygenUIElement.append(compiledElement);

                scope.$apply();
            })
        }

    }
});

CTCommonDirectives.directive("ctrendernestableshortcode", function($http) {
    return {
        restrict: "A",
        link: function(scope, element, attrs) {
            
            var id = parseInt(element.attr('ng-attr-component-id'));

            var callback = function(shortcode, contents) {
               
                
                if(typeof(contents) !== 'undefined') {
                    contents = contents.split('_#wrapped_content_replacer#_');
                    scope.$parent.component.options[id].model['wrapping_start'] = contents[0];
                    scope.$parent.component.options[id].model['wrapping_end'] = contents[1];
                } else {
                    scope.$parent.component.options[id].model['wrapping_start'] = '';
                    scope.$parent.component.options[id].model['wrapping_end'] = '';
                }

                scope.$parent.setOption(id, 'ct_nestable_shortcode', 'wrapping_start');
                scope.$parent.setOption(id, 'ct_nestable_shortcode', 'wrapping_end');
                
                scope.$parent.rebuildDOM(id);

            }
            
            var renderContent = function() {
                setTimeout(function() {
                    if(!scope.$parent) {
                        return;
                    }

                    var shortcode = scope.$parent.component.options[id].id['wrapping_shortcode'];

                    if(!shortcode) {
                        return;
                    }

                    var matches = [];

                    shortcode.replace(/\[(\w{1,})[^\]]*\]/ig, function(match, match2) {
                        matches.push(match);
                        matches.push(match2);
                        return '';
                    });

                    var shortcode_data = {
                        original: {
                            full_shortcode: matches[0]+"_#wrapped_content_replacer#_[/"+matches[1]+']',
                        }
                    }

                    scope.renderShortcode(id, 'ct_shortcode', callback, shortcode_data);
                }, 0);
            }

            var debounceChange = false;

            scope.$watch(element.attr('ct-nestable-shortcode-model'), function( newVal, oldVal ) {
                
                if(debounceChange === false && oldVal !== newVal) {
                    debounceChange = setTimeout(function() {
                        renderContent();   
                        debounceChange = false; 
                    }, 500)
                }                
            });

            //renderContent();
            
        }
    }
})

CTCommonDirectives.directive("ctrenderoxyshortcode", function($http, ctOxyCache) {
    return {
        restrict: "A",
        require: "ngModel",
        link: function(scope, element, attrs, ngModel) {
            
            var callback = function(shortcode, contents) {
                //ctOxyCache.store(shortcode, contents);
                element.html(contents);
            }

            setTimeout(function() {
                var id = parseInt(element.attr('ng-attr-component-id'));
                var shortcode = scope.$parent.getOption('ct_content', id);
                var shortcode_data = {
                    original: {
                        full_shortcode: shortcode
                    }
                }

                // add specific class only for content dynamic data
                if (shortcode.indexOf("data='content'")>0) {
                    
                    // hack needed to properly update components class in components tree
                    scope.$parent.currentClass = "oxy-stock-content-styles";
                    
                    scope.addClassToComponent(id,'oxy-stock-content-styles',false)
                    
                    // hack needed to properly update components class in components tree
                    scope.$parent.currentClass = false;
                }

                // var contents = ctOxyCache.get(shortcode);
                // if(contents) {
                //     callback(shortcode, contents);
                // }
                // else {

                scope.renderShortcode(id, 'ct_shortcode', callback, shortcode_data);

               // }
            }, 0);
        }
    }
})

/**
 * Make HTML5 "contenteditable" support ng-module
 * To enforce plain text mode, use attr data-plaintext="true"
 */

CTCommonDirectives.directive("contenteditable", function($timeout,$interval, ctScopeService) {

    return {
        restrict: "A",
        require: "ngModel",
        link: function(scope, element, attrs, ngModel) {

            element.unbind("paste input");

            function read() {
                ngModel.$setViewValue(element.html());
            }

            function getCaretPosition() {
                
                if(window.getSelection) {
                    selection = window.getSelection();
                    if(selection.rangeCount) {
                        range = selection.getRangeAt(0);
                        return(element.text().length-range.endOffset);
                    }
                }
            }

            function setCaretPosition(caretOffsetRight) {
                var range, selection;

                if(document.createRange) {
                    range = document.createRange();
                    if(element.get(0) && element.get(0).childNodes[0]) {
                        var offset = element.text().length;
                        
                        range.setStart(element.get(0), 0);
                        
                        if(caretOffsetRight > 0 && caretOffsetRight <= offset) {
                            offset -= caretOffsetRight;
                        }
                        range.setEnd(element.get(0).childNodes[0], offset);
                        range.collapse(false);
                        selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        
                    }
                    
                }
                else if(document.selection) {
                    range = document.body.createTextRange();
                    if(element.get(0) && element.get(0).childNodes[0]) {
                        var offset = element.text().length;
                            
                        range.setStart(element.get(0), 0);
                        
                        if(caretOffsetRight > 0 && caretOffsetRight <= offset) {
                            offset -= caretOffsetRight;
                        }
                        range.setEnd(element.get(0).childNodes[0], offset);
                        range.collapse(false);
                        range.select();
                    }
                }
            }

            ngModel.$render = function() {

                element.html(ngModel.$viewValue || "");

            };


            // save element content
            element.bind("input", function(e, paste) {

                scope.$apply(read);
                
                // if it is plaintext mode, replace any html formatting, only in paste mode
                if(paste && typeof(attrs['plaintext']) !== 'undefined' && attrs['plaintext'] === "true") {
                    
                    if(jQuery('<span>').html(element.html()).text().trim() !== element.html().trim().replace('&nbsp;', '')) {
                       // var caretPosition = getCaretPosition();
                       // element.html(jQuery('<span>').html(element.html()).text());
                       // setCaretPosition(caretPosition);
                        element.html(element.text());
                    }

                    ngModel.$setViewValue(element.text());
                }

                // if default text is provided and current text is blank. populate with defaulttext
                if(element.html().trim() === '' && typeof(attrs['defaulttext']) !== 'undefined' && attrs['defaulttext'].trim() !== '') {
                    element.text(attrs['defaulttext']);
                }

                // timeout for angular
                var timeout = $timeout(function() {
                    var dascope = scope,
                        optionName = attrs['optionname'] || "ct_content";

                    if(scope.iframeScope)
                        dascope = scope.iframeScope; 
                    dascope.setOption(dascope.component.active.id, dascope.component.active.name, optionName);
                    $interval.cancel(timeout);
                }, 20, false);
            })

            // trick to update content after paste event performed
            element.bind("paste", function() {
                setTimeout(function() {element.trigger("input", 'paste');}, 0);
            });
            
            // if data-plaintext is NOT set to "true"
            if(typeof(attrs['plaintext']) === 'undefined' || attrs['plaintext'] !== "true") {

                // enable content editing on double click
                element.bind("dblclick", function() {
                    
                    var parentScope = ctScopeService.get('scope').parentScope,
                        optionName = attrs['optionname'] || "ct_content";
                    
                    // before enabling edit content,
                    var content = scope.getOption(optionName);
                    
                    content = content.replace(/\<span id\=\"ct-placeholder-([^\"]*)\"\>\<\/span\>/ig, function(match, id) {
                        
                        var oxy = scope.component.options[parseInt(id)]['model']['ct_content'];

                        var containsOxy = oxy.match(/\[oxygen[^\]]*\]/ig);

                        if(containsOxy) {
                            scope.removeComponentById(parseInt(id), 'span', scope.component.active.id);
                            return oxy;
                        }
                        else {
                            return match;
                        }

                    });

                    scope.setOptionModel(optionName, content, scope.component.active.id, scope.component.active.name)

                    parentScope.enableContentEdit(element);
                    scope.$apply();
                });

                // format as <p> on enter/return press
                if ( element[0].attributes['ng-attr-paragraph'] ) {
                    element.bind('keypress', function(e){
                        if ( e.keyCode == 13 ) {
                            document.execCommand('formatBlock', false, 'p');
                        }
                    });
                }
                else {
                    // format as <br/>
                    element.bind('keypress', function(e){
                        if ( e.keyCode == 13 ) { 
                            document.execCommand('insertHTML', false, '<br><br>');
                            return false;
                        }
                    });
                }
            } 
            // else if it is plaintext mode
            else {
                // we do not need line breaks
                element.bind('keypress', function(e){
                    
                    if ( e.keyCode == 13 ) { 
                        element.blur();
                        return false;
                    }
                });
            }
            
            // if ngBlur is provided
            if(typeof(attrs['ngBlur']) !== 'undefined' || attrs['ngBlur'] !== "") {
                element.bind('blur', function() {
                    var timeout = $timeout(function() {
                        scope.$apply(attrs.ngBlur);
                        $interval.cancel(timeout);
                    }, 0, false);
                })
            }

        }
    };
});

/**
 * Helps an input text field gain focus based on a condition
 * 
 * @since 0.3.3
 * @author Gagan Goraya
 *
 * usage: <input type="text" focus-me="booleanValue" />
 */
 
CTCommonDirectives.directive('focusMe', function($timeout) {
  return {
    scope: { trigger: '=focusMe' },
    link: function(scope, element) {
      scope.$watch('trigger', function(value) {
        if(value === true) { 
          $timeout(function() {
            element[0].focus();
            scope.trigger = false;
          });
        }
      });
    }
  };
});
