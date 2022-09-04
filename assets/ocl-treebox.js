
/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

OclTreeBox = 
{
    init : function()
    {
        var treeboxParent = $('.osy-treebox').parent();
        $(treeboxParent).on('click','span.osy-treebox-branch-command',function(){
            OclTreeBox.toggleBranch($(this));
        }).on('click','span.osy-treebox-label', function(){
            OclTreeBox.clickLabel($(this));
        });
    },
    toggleBranch : function(elm)
    {             
        var box = $(elm).closest('.osy-treebox');
        var nodeId = $(elm).closest('.osy-treebox-node').data('nodeId');        
        var hdnOpenNodes = $('input.openNodes', box);
        var strOpenNodes = hdnOpenNodes.val();
        if ($(elm).hasClass('minus')){
           hdnOpenNodes.val(strOpenNodes.replace('['+nodeId+']',''));
        } else {
           hdnOpenNodes.val(strOpenNodes + '['+nodeId+']');
        }        
        $(elm).toggleClass('minus');
        $(elm).parent().next().toggleClass('hidden').toggleClass('d-none');
        this.refreshComponents($(box).data('refreshOnOpen'));
    },
    clickLabel : function(elm)
    {        
        var box = $(elm).closest('.osy-treebox');
        $('span.osy-treebox-label', box).removeClass('osy-treebox-label-selected');                   
        var curNodeId = String($(elm).closest('.osy-treebox-node').data('nodeId'));
        var selNodeId = $('input.selectedNode', box).val();        
        if (curNodeId !== selNodeId) {
            $(elm).addClass('osy-treebox-label-selected');
            $('input.selectedNode', box).val(curNodeId);
        } else {
            $('input.selectedNode', box).val('');
        }
        this.refreshComponents($(box).data('refreshOnClick'));
    },
    refreshComponents: function(strComponents)
    {
        if (!strComponents) {
            return;
        }
        var rawComponents = strComponents.split(',');
        var components = [];
        for (var i in rawComponents) {
            if ($('#'+rawComponents[i]).hasClass('osy-datagrid-2')) {
                ODataGrid.refreshAjax($('#'+rawComponents[i]));
                continue;
            }
            components.push(rawComponents[i]);
        }
        if (components.length > 0){
            Osynapsy.refreshComponents(components);
        }
    }
};

if (window.Osynapsy) {
    Osynapsy.plugin.register('OclTreeBox',function(){
        OclTreeBox.init();
    });
}