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
        ui.CheckAll = root.find(".check_all");
        ui.ProductCheckboxes = root.find(".order-items-table tbody input[type='checkbox']");

        // Asignamos los manejadores de eventos
        ui.ResolutionType.change(updateSizeCells);
        ui.Form.submit(validate);
        ui.CheckAll.change(toggleAll);

        updateSizeCells();
    }

    function toggleAll()
    {
        ui.ProductCheckboxes.prop("checked", this.checked);
    }

    function updateSizeCells()
    {
        ui.SizeCells.toggle(getResolutionType() == RmaSystem.Constants.ResolutionTypeExchange);
    }

    function getCheckedProducts()
    {
        return ui.Root.find('[name^="item_checked["]:checked');
    }

    function getResolutionType()
    {
        return ui.ResolutionType.filter(":checked").val();
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
            if(getResolutionType() == RmaSystem.Constants.ResolutionTypeExchange)
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
