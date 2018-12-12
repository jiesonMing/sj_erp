$(function(){
	//验证 函数
	function validate_data(data,is_type){
		//字符串
		if(is_type=='string'){
			var reg=/^[\u4E00-\u9FA5]{1,5}$/;
			if(!reg.test(data)) {
				return false;
			}
		}
		//特殊字符
		if(is_type=='spec_str'){
			var reg=/['"#?$%&\^*>,"<《？，。！@#￥%’”/；]/;
			if(reg.test(data)){
				return false;
			} 
		}
		//不能为空
		if(is_type=='not_empty'){
			if(data=='') {
				return false;
			}
		}
		return true;
	}
	function layerMessage(el){
		var res=eval("("+el+")");
		if(res.statusCode==200){
			layer.msg(res.retMessage,{icon:1},function(){
				window.location.reload();
			})
		}else{
			layer.msg(res.retMessage,{icon:0},function(){
				window.location.reload();
			})
		}
	}
	//选择删除
   	function checkedIpt(a,tp){
		var inp=$("tbody tr td").find("input");
		var len=$("tbody tr td").find("input").length;
		var str='';
		for(var i=0;i<len;i++){
		    if(inp[i].checked){
		        str+=inp[i].title+',';
		    }
		}
		str=str.substring(str.length-1,",");
		if(str!=''){
		    $.ajax({
		       type: tp,
		       url: "/Index/Goods/"+a,
		       data: "id="+str,
		       success: function(el){            
		            layerMessage(el);                               
		       }
		    });
		}else{
		    layer.msg('请选择数据！',{icon:0,time:2000});
		}          
	}
	//全选
	$("#checkbox_a0").click(function(){
        if($(this).prop("checked")==true){
            $("tr td").find("input").prop("checked",true);
        }else{
            $("tr td").find("input").prop("checked",false);
        }         
    });
	/*
	*商品分类
	*/
	$('.topCate').on('change',function(){
		var topId=$(this).children('option:selected').val();
		if(!topId) layer.msg('请重新选择',function(){return false});
		$.post("/Index/Goods/addCategory",{topId:topId},function(el){
			var str='<option value="0">二级分类</option>';
			$.each(el,function(i,item){
				str+="<option value='"+item.topId+"'>"+item.cName+"</option>";
			});
			$('.secondCate').empty();
			$('.secondCate').append(str);
		});
	})
	//添加分类提交
	is_submit_addcate=false;
	$('.addCate_submit').click(function(){
		if(is_submit_addcate){
			layer.msg('切勿重复提交');return false;
		}
		var cName=$("input[name=cName]").val();
		var topId=$('.topCate').children('option:selected').val();
		var secondId=$('.secondCate').children('option:selected').val();
		//if(!validate_data(cName,'string')) layer.msg('字符含有非中文字符');return false;
		$.post("/Index/Goods/cate_handle_post",{cName:cName,topId:topId,secondId:secondId},function(el){
			layerMessage(el);
			is_submit_addcate=true;
		})
		
		
	})
	//移除
	$('.remove_status').each(function(i){
		$(this).click(function(){
			var cateId=$(this).parent().attr('name');
			layer.confirm('确定删除？',function(yes){
				$.post("/Index/Goods/delCategory",{cateId:cateId},function(el){
					layerMessage(el);
				})
			});
		})
	})
	
    // 添加商品品牌
    $(".addGoodsBrand_submit").click(function ()
    {
    	var data=$('.edit_add>form').serializeArray();
		var reg=/['"#?$%&\^*>,"<《？，。！@#￥%’”/；]/;
		if(data[0].value==''){
			layer.msg('品牌名称不能为空',{icon:0,time:2000});return false;
		}
		if(reg.exec(data[1].value)){
			layer.msg('品牌描述不能含有非法字符',{icon:0,time:2000});return false;
		}
		if(reg.exec(data[2].value)){
			layer.msg('品牌地址不能含有非法字符',{icon:0,time:2000});return false;
		}
		$.post("/Index/Goods/addGoodsBrand_handle",$('.edit_add>form').serializeArray(),function(el){
			//console.log(el);
			layerMessage(el);
		})
    });
    $(".editGoodsBrand_submit").click(function ()
    {
    	var data=$('.edit_add>form').serializeArray();
		var reg=/['"#?$%&\^*>,"<《？，。！@#￥%’”/；]/;
		if(data[0].value==''){
			layer.msg('品牌名称不能为空',{icon:0,time:2000});return false;
		}
		if(reg.exec(data[1].value)){
			layer.msg('品牌描述不能含有非法字符',{icon:0,time:2000});return false;
		}
		if(reg.exec(data[2].value)){
			layer.msg('品牌地址不能含有非法字符',{icon:0,time:2000});return false;
		}
		$.post("/Index/Goods/editGoodsBrand_handle",$('.edit_add>form').serializeArray(),function(el){
			//console.log(el);
			layerMessage(el);
		})
    });

    //添加商品
    is_submit_addGoods=false;
    $('.addGoods_submit').click(function(){
    	if(is_submit_addGoods){
			layer.msg('切勿重复提交');return false;
		}
    	$.post("/Index/Goods/addGoods_handle",$(this).parent().parent().serializeArray(),function(el){
			layerMessage(el);
			is_submit_addGoods=true;
		})
		
    })
    //编辑商品
    $('.editGoods').each(function(i){
    	$(this).click(function(){
    		var href_url='/Index/Goods/editGoods/id/'+$(this).parent().attr('title');
    		window.location.href=href_url;
    	})
    })
    //编辑商品提交
    $('.editGoods_submit').click(function(){
    	//alert($(this).parent().parent().serializeArray());
    	$.post("/Index/Goods/editGoods_handle",$(this).parent().parent().serializeArray(),function(el){
			layerMessage(el);
			//is_submit_addGoods=true;
		})
    })
    //商品列表新增sku
    $('.addgoodsSKU').each(function(i){
    	$(this).click(function(){
    		var href_url='/Index/Goods/addGoodsSKU/id/'+$(this).attr('title');
    		window.location.href=href_url;
    	})
    })
    //删除商品
    $(".delGoods").click(function(){
        if($("tbody input").is(':checked')){
            layer.confirm('确定删除？',{title:'提示'},function(yes){
                var a='del_goods';//ajax地址
                var tp='post';//数据传送方式
                checkedIpt(a,tp);
            });               
        }else{
            layer.msg('请选择数据！',{time:2000});
        }
    });
    //提交sku
    is_submit_addsSKU=false;
    $('.addGoodsSKU_submit').click(function(){
    	if(is_submit_addsSKU){
			layer.msg('切勿重复提交');return false;
		}
    	var sku=$('input[name=sku]').val();
    	var warehouseId=$('select[name=warehouseId]').children('option:selected').val();
    	if(!validate_data(warehouseId,'not_empty')){
    		layer.msg('仓储企业不能为空',{icon:0});return false;
    	}
    	if(!validate_data(sku,'spec_str')){
    		layer.msg('sku编码不能含有非法字符',{icon:0});return false;
    	}
    	$.post("/Index/Goods/addGoodsSKU_handle",$(this).parent().parent().parent().find('form').serializeArray(),function(el){
    		layerMessage(el);
    	});
    	is_submit_addsSKU=true;
    })
    //修改sku
    $('.editGoodsSKU').each(function(i){
    	$(this).click(function(){
    		var editedVal=$(this).parent().siblings('.editGoodsSKU_input').text();
    		var edit_input="<input type='text' class='input edit_sku_input' name='sku' value='"+editedVal+"'/>";
    		$(this).parent().siblings('.editGoodsSKU_input').text('').append(edit_input);
    	})
    })
    //修改sku的input的值变化
    $('.goods_sku').on('blur','.edit_sku_input',function(){
    	var sku=$(this).val();
    	var skuId=$(this).parent().attr('title');
    	$.post("/Index/Goods/editGoodsSKU_handle",{sku:sku,skuId:skuId},function(el){
    		layerMessage(el);
    	});
    	//$(this).parent().text(sku).remove('input');
    })
    //删除sku
    $(".delGoodsSKU").click(function(){
        if($("tbody input").is(':checked')){
            layer.confirm('确定删除？',{title:'提示'},function(yes){
                var a='del_goods_sku';//ajax地址
                var tp='post';//数据传送方式
                checkedIpt(a,tp);
            });               
        }else{
            layer.msg('请选择数据！',{time:2000});
        }
    });
    //编辑商品品牌
    $('.editGoodsBrand').each(function(i){
    	$(this).click(function(){
    		var href_url='/Index/Goods/editGoodsBrand/id/'+$(this).parent().attr('title');
    		window.location.href=href_url;
    	})
    })

    //删除品牌
    $(".delGoodsBrand").click(function(){
        if($("tbody input").is(':checked')){
            layer.confirm('确定删除？',{title:'提示'},function(yes){
                var a='del_goods_brand';//ajax地址
                var tp='post';//数据传送方式
                checkedIpt(a,tp);
            });               
        }else{
            layer.msg('请选择数据！',{time:2000});
        }
    });
    //打开addGoodsboom的子商品
    $('.goods_in_open').click(function(){
    	layer.open({
    		type:2,
    		title:'添加子商品',
    		area:['70%','70%'],
    		content:'/Index/Goods/addGoodsBoom_open',
    	});
    })

    //选择子商品
    $('.choose_childGoods').each(function(i){
    	$(this).click(function(){
    		var goodsId=$(this).attr('title');
    		if($(this).is(':checked')){
    			var goodsName=$(this).parent().siblings().eq(2).text();
    			var tr_str="<tr id='tr_"+goodsId+"'>";
    				tr_str+="<td class='border_left'>0</td>";
    				tr_str+="<td>"+goodsName+"</td>";
    				tr_str+="<td><input type='text' name='childGoods_num[]' class='input w30'/></td>";
    				tr_str+="<input type='hidden' name='goodsId[]' value='"+goodsId+"'/>";
    				tr_str+="</tr>";
    			$(".checked_table>tbody").append(tr_str);
    		}else{
    			$('#tr_'+goodsId).remove();
    		}
    		flash_chioseGoods();
    	})
    })
    //刷新已选择的id
    function flash_chioseGoods(){
    	var len=$(".checked_table>tbody>tr").length;
    	$(".checked_table>tbody>tr").each(function(i){
    		if(len>0){
    			$(this).children().eq(0).text($(this).index()+1)	
    		}

    	})
    }
    //保存子商品
    is_pass=false;
    $('.addGoodsBoom_child_submit').click(function(){
    	$(".checked_table>tbody>tr").each(function(i){
    		var in_val=$(this).children().find('input').val();
    		if(!validate_data(in_val,'spec_str')){
    			is_pass=false;
	    		layer.msg('商品数量不能含有非法字符',{icon:0});return false;
	    	}
    		if(in_val==''||in_val==0){
    			layer.msg('数量不能为空或者为0',{icon:0,time:2000},function(){
    				$(this).children().find('input').focus();
    			});
    			is_pass=false;
    			return false;
    		}else{
    			is_pass=true;
    		}
    	});
    	if(is_pass){
    		//提交
    		$.post("/Index/Goods/addGoodsBoom_dataHandle",$(".checked_table").parent().serializeArray(),function(el){
    			var res=eval("("+el+")");
    			console.log(el);
    			//把数据显示在父页
    			var tr_str='';
    			$.each(res.data,function(index,value){
    				tr_str+="<tr class='tr1'>";
					tr_str+="<td>"+(index+1)+"</td>";
					tr_str+="<td>"+value['goodsCode']+"</td>";
					tr_str+="<td>"+value['goodsName']+"</td>";
					tr_str+="<td>"+value['specType']+"</td>";
					tr_str+="<td>"+value['unitName']+"</td>";
					tr_str+="<td>"+value['price']+"</td>";
					tr_str+="<td>"+value['num']+"</td>";
					tr_str+="<td align='center'><a href='#' class='a_list c_1785c8 goods_in_open' title='删除'><i class='fa fa-times-circle'></i></a></td>";
					tr_str+="<input type='hidden' name='childGoods[]' value='"+value['goodsArr']+"'/>";
					tr_str+="</tr>";
    			})
    			$(".parentChildGoods" , parent.document).append(tr_str);
    			var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
   				parent.layer.close(index);layer.closeAll();
    		});  		
   		}
    })
    //删除boom
    $(".delGoodsBoom").click(function(){
        if($("tbody input").is(':checked')){
            layer.confirm('确定删除？',{title:'提示'},function(yes){
                var a='del_goods_boom';
                var tp='post';
                checkedIpt(a,tp);
            });               
        }else{
            layer.msg('请选择数据！',{time:2000});
        }
    });
    //保存addGoodsBoom
    $('.addGoodsBoom_submit').click(function(){
    	if(!validate_data($('select[name=cateId]').children('option:selected').val(),'not_empty')){
    		layer.msg('商品分类不能为空',{icon:0});return false;
    	}
    	if(!validate_data($('input[name=goodsName]').val(),'not_empty')){
    		layer.msg('商品名称不能为空',{icon:0});return false;
    	}
    	if(!validate_data($('input[name=goodsCode]').val(),'not_empty')){
    		layer.msg('商品自编码不能为空',{icon:0});return false;
    	}
    	if(!validate_data($('select[name=stockUnit]').children('option:selected').val(),'not_empty')){
    		layer.msg('商品计量单位不能为空',{icon:0});return false;
    	}
    	if(!validate_data($('select[name=country]').children('option:selected').val(),'not_empty')){
    		layer.msg('商品原产国不能为空',{icon:0});return false;
    	}
    	// console.log($(this).parent().siblings().serializeArray());
    	$.post("/Index/Goods/addGoodsBoom_handle",$(this).parent().siblings().serializeArray(),function(el){
    		console.log(el);return false;
    		layerMessage(el);
    	})
    })
})