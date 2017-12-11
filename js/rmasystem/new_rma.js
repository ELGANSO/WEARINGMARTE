var RmaSystem = RmaSystem || {};

RmaSystem.newRmaPage = (function($, rootContainer)
{
    var ResolutionTypeRefund = 0;
    var ResolutionTypeExchange = 1;

    var ui = {
        ResolutionType: rootContainer.find("[name='resolution_type']"),
        SizeHeader: rootContainer.find(".size-header"),
        PickupContainer: rootContainer.find(".pickup"),
        Loader: rootContainer.find(".wk_rma_bg"),
        CheckAll: rootContainer.find("#check_all"),
        Address: {
            Address: rootContainer.find("#wk_rma_pickup_address"),
            Number: rootContainer.find("#wk_rma_pickup_number"),
            PostCode: rootContainer.find("#wk_rma_pickup_postcode"),
            City: rootContainer.find("#wk_rma_pickup_city"),
            Phone: rootContainer.find("#wk_rma_pickup_phone"),
            Email: rootContainer.find("#wk_rma_pickup_email")
        }
    };

    var templates = {
        OrderItems: Handlebars.compile(document.getElementById("template-order-items").innerHTML),
        SizeSelect: Handlebars.compile(document.getElementById("template-size-select").innerHTML)
    };

    function initialize()
    {
        ui.ResolutionType.change(updateSizeCells);
        ui.CheckAll.change(onCheckAllChanged);
    }

    function updateSizeCells()
    {
        if(isExchange())
        {
            ui.SizeHeader.show();
            getSizeSelectors().show();
        }
        else
        {
            ui.SizeHeader.hide();
            getSizeSelectors().hide();
        }
    }

    function onCheckAllChanged()
    {
        getItemCheckboxes().each(function(i, checkbox)
        {
            checkbox.checked = ui.CheckAll.prop("checked");
            if(!canBeChecked(checkbox))
            {
                checkbox.checked = false;
            }
        });
    }

    function isExchange()
    {
        return ui.ResolutionType.val() == ResolutionTypeExchange;
    }

    function getSizeSelectors()
    {
        return rootContainer.find(".item_size");
    }

    function loadOrder(check_radio, getItemDetailsUrl)
    {
        ui.Loader.show();
        $('#rma_product_exchange').parent().parent('li').siblings('.wk_li').remove();
        $('#rma_product_exchange option[value=""]').prop('selected', true);
        var increment_id = $(check_radio).attr("data-inc_id");
        var ord_id = $(check_radio).attr("data-ord_id");

        jQuery.ajax({
            url: getItemDetailsUrl,
            type: "POST",
            dataType: "json",
            data: {order_id: ord_id},
            success: function (order)
            {
                // Si el pedido ha sido enviado mostramos el panel con los datos de
                // recogida, si no lo ocultamos
                ui.PickupContainer.toggle(order.has_shipped);

                // Rellenamos la dirección por defecto en la que se recogerá el pedido
                // (la dirección de envío)
                ui.Address.Address.val(order.address);
                ui.Address.Number.val(order.number);
                ui.Address.PostCode.val(order.postcode);
                ui.Address.City.val(order.city);
                ui.Address.Phone.val(order.phone);


                // Mostramos los productos del pedido
                var items = order.items;

                $.each(items, function(i, item)
                {
                    item.sizeColumnStyle = isExchange()
                        ? ""
                        : "display: none;";
                    item.sizeSelectHtml = getSizeSelectHtml(item.itemid, item.available_sizes);
                });

                var html = templates.OrderItems({
                    order_id: ord_id,
                    increment_id: increment_id,
                    items: items
                });

                $("#wk_rma_order_details").find("tbody").html(html);
                decorateTable("wk_rma_order_details");
                ui.Loader.hide();

                getItemCheckboxes().change(verifyAvailableSizes);
                updateSizeCells();
            }
        });
    }

    function getItemCheckboxes()
    {
        return $("[name^='item_checked[']");
    }

    function getSizeSelectHtml(itemId, sizes)
    {
        return templates.SizeSelect({
            name: "requested_size[" + itemId + "]",
            sizes: sizes
        });
    }

    // Comprobamos el número de opciones de talla y si no hay al menos dos mostramos
    // un mensaje de error y desmarcamos la casilla clickada
    function verifyAvailableSizes()
    {
        if(!canBeChecked(this))
        {
            alert($("#message-no-other-available-sizes").val());
            this.checked = false;
        }
    }

    function canBeChecked(checkbox)
    {
        var sizeSelect = $(checkbox).closest("tr").find(".size-select");

        if(!sizeSelect.is(":visible"))
        {
            return true;
        }

        var numberOfOptions = sizeSelect.find("option").length;
        return numberOfOptions > 1;
    }

    return {
        initialize: initialize,
        isExchange: isExchange,
        loadOrder: loadOrder,
        updateSizeCells: updateSizeCells
    }
})(jQueryRma, jQueryRma("#wk_new_rma_container"));