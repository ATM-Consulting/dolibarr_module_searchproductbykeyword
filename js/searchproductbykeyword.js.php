<?php
	require '../config.php';

	$langs->load('searchproductbykeyword@searchproductbykeyword');

?>
var spk_line_class = 'even';
$(document).ready(function() {

    initSearchProductByKeyword("div#ProductList");
	
	$('#addline_spk').click(function() {
		$(this).after('<span class="loading"><?php echo img_picto('', 'working.gif') ?></span>');
		$(this).hide();
		var TProduct={};
		var TProductPrice={};
		var TProductQty={};
		
		$('input.checkSPK:checked').each(function(i,item){
			var fk_product = $(item).attr('fk_product');
			TProduct[fk_product] = fk_product;
            TProductQty[fk_product] = $('input[name="TProductSPKQty['+fk_product+']"]').val();
		});
		
		<?php if (!empty($conf->global->PRODUIT_MULTIPRICES)) { ?>
		$('input.radioSPK:checked').each(function(i,item){
			var priceToUse = $(item).val();
			TProductPrice[$(item).data('fk-product')] = priceToUse;
		});
		<?php } ?>

		$.ajax({
			url:"<?php echo dol_buildpath('/searchproductbykeyword/script/interface.php',1); ?>"
			,data:{
				put:"addline"
				,TProduct:TProduct
				,TProductPrice:TProductPrice
                ,TProductQty:TProductQty
				,object_type:spk_object_type
				,object_id:spk_object_id
				,qty:$('#qty_spk').val()
				<?php if (!empty($conf->global->SUBTOTAL_ALLOW_ADD_LINE_UNDER_TITLE)) { ?>,under_title:$(this).closest('td').children('select.under_title').val()<?php } ?>
			}
			,method:'post'
			,dataType:'json'	
		}).done(function(data) {
			
			var url = window.location.href;
			
			url = url.replace(window.location.hash, "");
			window.location.href=url;
			
			return;
		});
		
	});
});

function searchProduct(a) {
    var keyword = $(a).prev('input[name=spk_keyword]').val();
    getProducts($("div#ProductList"), keyword);
}

function checkProductSPK(fk_product) {
    if( $('input[name="TProductSPKtoAdd['+fk_product+']"]').is(':checked') ) {
        $('input[name="TProductSPKtoAdd['+fk_product+']"]').prop('checked',false);
    }
    else {
        $('input[name="TProductSPKtoAdd['+fk_product+']"]').prop('checked',true);
    }

}

function getProducts(container, keyword) {

    container.find('#listProd').remove();
    container.append('<span class="loading"><?php echo img_picto('', 'working.gif'); ?></span>');
    let is_supplier = $('input#fourn').val();
    if(is_supplier === undefined) is_supplier = 0;

    $.ajax({
        url:"<?php echo dol_buildpath('/searchproductbykeyword/script/interface.php',1); ?>"
        ,data:{
            get:"products"
            ,keyword:keyword
            ,fk_soc:spk_fk_soc
            ,is_supplier:is_supplier
        }
        ,dataType:'json'
    }).done(function(data) {

        var $list = $('<ul id="listProd"></ul>');

        if(data.TProduct.length ==0) {
            $list.append('<li class="none '+spk_line_class+'"><?php echo $langs->trans('NothingHere'); ?></li>');
        }
        else
        {
            $head = $('<table width="100%"><tr><td width="80%"><?php echo $langs->trans("Label") ?></td><td width="20%"><?php echo $langs->trans("Qty") ?></td></tr></table>');
            $list.append($head);
            $.each(data.TProduct,function(i,item) {
                spk_line_class = (spk_line_class == 'even') ? 'odd' : 'even';

                var TRadioboxMultiPrice = '';
                <?php if (!empty($conf->global->PRODUIT_MULTIPRICES)) { ?>
                if (is_supplier == 0){
                    for (var p in item.multiprices) {
                        if (item.multiprices_base_type[p] == 'TTC') var priceToUse = parseFloat(item.multiprices_ttc[p]);
                        else var priceToUse = parseFloat(item.multiprices[p]);

                        if (isNaN(priceToUse)) priceToUse = 0;

                        var checked = false;
                        if (data.default_price_level == p) checked = true;
                        TRadioboxMultiPrice += '<span class="multiprice"><input '+(checked ? "checked" : "")+' class="radioSPK" type="radio" name="TProductSPKPriceToAdd['+item.id+']" value="'+priceToUse+'" data-fk-product="'+item.id+'" style="vertical-align:bottom;" /> ' + priceToUse.toFixed(2) + '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                }
                <?php } ?>

                inputQty = '<input type="text" name="TProductSPKQty['+item.id+']" fk_product="'+item.id+'">';

                $li = $('<li class="product '+spk_line_class+'" productid="'+item.id+'"> <table width="100%"><tr><td width="80%"><input type="checkbox" value="1" name="TProductSPKtoAdd['+item.id+']" fk_product="'+item.id+'" class="checkSPK" /> <a class="checkIt" href="javascript:;" onclick="checkProductSPK('+item.id+')" >'+item.label+'</a> '+TRadioboxMultiPrice+'</td><td width="20%">'+inputQty+'</td></tr></table></li>');

                <?php if (!empty($conf->global->SPK_DISPLAY_DESC_OF_PRODUCT)) { ?>
                var desc = item.description.replace(/'/g, "\\'");

                <?php 	if(!empty($conf->global->PRODUCT_USE_UNITS)){ ?>
                desc = desc + "\n Unit : "+item.unit;
                <?php } ?>
                var bubble = $("<?php echo addslashes(img_help()); ?>");
                bubble.attr('title', desc);

                $li.append(bubble);
                <?php } else if (!empty($conf->global->PRODUCT_USE_UNITS)) { ?>
                var unit = "Unit : "+item.unit;
                var bubble = $("<?php echo addslashes(img_help()); ?>");
                bubble.attr('title', unit);
                $li.append(bubble);
                <?php } ?>

                $list.append($li);
            });
        }

        container.find('span.loading').remove();
        container.append($list);

    });
}

function initSearchProductByKeyword(selector) {

	$arbo = $( selector );

	$arbo.html();
	$arbo.append('<div><input type="text" value="" name="spk_keyword" size="10" /> <a href="javascript:;" onclick="searchProduct(this)"><?php echo img_picto('','search'); ?></a></div>');

}
