define(['jquery','core/log','mod_NEWMODULE/cloudpoodllloader'], function($,log,cloudpoodll) {
    "use strict"; // jshint ;_;

    log.debug('NEWMODULE grade now helper: initialising');

    return {
        init: function(){
             cloudpoodll.autoCreateRecorders();
        }
    };//end of return object

});