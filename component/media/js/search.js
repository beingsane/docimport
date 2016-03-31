/**
 * @package		DocImport
 * @copyright	Copyright (c)2010-2016 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

/**
 * Setup (required for Joomla! 3)
 */
if(typeof(akeeba) == 'undefined') {
    var akeeba = {};
}

if(typeof(akeeba.jQuery) == 'undefined') {
    akeeba.jQuery = jQuery.noConflict();
}

if (typeof akeeba.DocImport == 'undefined') {
    akeeba.DocImport = {}
}

if (typeof akeeba.DocImport.Search == 'undefined')
{
    akeeba.DocImport.Search = {
        "labelAllSections": ''
    }
}

akeeba.DocImport.Search.sectionsChange = function ()
{
    (function($){
        var forDisplay = [];
        var element = $('#dius-searchutils-areas');
        var selections = element.val();

        element.children().each(function(name, val){
            if (selections != null)
            {
                if (selections.indexOf(val.value) >= 0)
                {
                    forDisplay.push(val.text);
                }
            }
        });

        if (selections != null)
        {
            if (selections.indexOf('*') != -1)
            {
                forDisplay = [akeeba.DocImport.Search.labelAllSections];
            }
        }

        if (!forDisplay.length)
        {
            forDisplay = [akeeba.DocImport.Search.labelAllSections];
        }

        $('#dius-searching-areas').html(forDisplay.join(', '));

    }(akeeba.jQuery));
};