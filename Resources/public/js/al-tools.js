/*
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

;(function($)
{
    $.fn.ResetFormElements =function()
    {
        this.each(function()
        {
            $(this).find(":input:not(input[type=submit])").val("").removeAttr("checked");
            $(this).find("select").val("");
            $(this).find("textarea").val("");
        });
    };
    
    $.fn.center = function(parent, xGap, yGap) 
    {
        if (parent) {
            parent = this.parent();
        } else {
            parent = window;
        }
        
        if (xGap == null) {
            xGap = 0;
        }
        
        if (yGap == null) {
            yGap = 0;
        }
        
        this.css({
            "position": "absolute",
            "top": (((($(parent).height() - this.outerHeight()) / 2) + $(parent).scrollTop()) + yGap + "px"),
            "left": (((($(parent).width() - this.outerWidth()) / 2) + $(parent).scrollLeft()) + xGap + "px")
        });
        return this;
    };
})($);
