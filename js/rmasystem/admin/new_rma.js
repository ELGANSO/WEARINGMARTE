var RmaSystem = RmaSystem || {};
RmaSystem.Admin = RmaSystem.Admin || {};

RmaSystem.Admin.newRmaPage = (function()
{
    var $;
    var ui = { };

    function initialize(jQuery, root)
    {
        $ = jQuery;
        root = $(root);

        // Obtenemos referencias a los elementos de UI
        ui.Root = root;
        ui.Form = root;
        ui.ResolutionType = root.find('[name="resolution_type"]');
        ui.SizeCells = root.find(".size-cell");
        ui.QtyCells = root.find(".qty-cell");
        ui.CheckAll = root.find(".check_all");
        ui.ProductCheckboxes = root.find(".order-items-table tbody input[type='checkbox']");

        // Asignamos los manejadores de eventos
        ui.ResolutionType.change(updateSizeAndQtyCells);
        ui.Form.submit(validate);
        ui.CheckAll.change(toggleAll);
        ui.ProductCheckboxes.change(toggleSelectors);

        updateSizeAndQtyCells();
    }

    function toggleAll()
    {
        ui.ProductCheckboxes.prop("checked", this.checked);
    }

    function toggleSelectors()
    {
        var itemId = $(this).data("item-id");
        var sizeSelector = $("#requested_size_" + itemId);
        var qtySelector = $("#return_item_" + itemId);
        sizeSelector.prop("disabled", !this.checked);
        qtySelector.prop("disabled", !this.checked);
    }

    function updateSizeAndQtyCells()
    {
        var isExchange = getResolutionType() === RmaSystem.Constants.ResolutionTypeExchange;
        var isRefund = getResolutionType() === RmaSystem.Constants.ResolutionTypeRefund;
        ui.SizeCells.toggle(isExchange);
        ui.QtyCells.toggle(isRefund);
    }

    function getCheckedProducts()
    {
        return ui.Root.find('[name^="item_checked["]:checked');
    }

    function getResolutionType()
    {
        return parseInt(ui.ResolutionType.filter(":checked").val(), 10);
    }

    function getRequestedSizeValueForItemId(itemId)
    {
        return ui.Root.find("[name='requested_size[" + itemId + "]']").val();
    }

    function validate()
    {
        try
        {
            // Comprobamos que se haya seleccionado la acción
            if (ui.ResolutionType.filter(":checked").length === 0)
            {
                alert("Por favor, seleccione la acción a realizar.");
                return false;
            }

            // Comprobamos que se haya marcado al menos un producto
            if(getCheckedProducts().length === 0)
            {
                alert("Por favor, marque al menos un producto.");
                return false;
            }

            // Si la acción a realizar es el cambio de talla, comprobamos que
            // todos los items marcados tengan seleccionada una nueva talla
            if(getResolutionType() === RmaSystem.Constants.ResolutionTypeExchange)
            {
                var checkedProducts = getCheckedProducts();
                for(var i=0; i<checkedProducts.length; i++)
                {
                    var itemId = checkedProducts.eq(i).data("item-id");
                    if(getRequestedSizeValueForItemId(itemId) === "")
                    {
                        alert("Por favor, seleccione la nueva talla deseada.");
                        return false;
                    }
                }
            }

            return true;
        }
        catch(ex)
        {
            alert(ex);
            return false;
        }
    }

    return {
        initialize: initialize
    }
})();