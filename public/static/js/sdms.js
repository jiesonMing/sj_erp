$(function(){
	$(".trhide").hide();
	$(".tr1").each(function(i){
        if(i%2==0){
            $(this).addClass("tr_2");
        }else{
            $(this).addClass("tr_1");
        }
	});
	$(".td1").each(function(i){
		$(this).click(function(){
			if($(".trhide").eq(i).is(":hidden")){
				$(".trhide").eq(i).show();	
			}else{
				$(".trhide").eq(i).hide();	
			}
		});
	});
    $(".tips").each(function(){
        $(this).tipso({
            useTitle: false,
            position: 'right'
        });
    });
    $(".nav_a").each(function(index,item){
        $(this).click(function(){
            if($(".nav_dd").eq(index).hasClass("nav_none")){
                /*$(".nav_a").each(function(i,it){
                    if(!$(this).hasClass("nav_none")){*/
                        $(".nav_dd").addClass("nav_none");
                        $(".nav_a").children('i').removeClass("fa-caret-down");
                        $(".nav_a").children('i').addClass("fa-caret-right");
              /*      }
                });*/
                $(".nav_dd").eq(index).removeClass("nav_none");
                $(this).children('i').removeClass("fa-caret-right");
                $(this).children('i').addClass("fa-caret-down");
            }else{
                $(".nav_dd").eq(index).addClass("nav_none");
                $(this).children('i').removeClass("fa-caret-down");
                $(this).children('i').addClass("fa-caret-right");
            }
        });
    });
    $(".checkbox0").click(function(){
        if($(this).is(':checked')){
            $("input:checkbox").each(function(){
                this.checked = true;
            });
        }else{
            $("input:checkbox").each(function(){
                this.checked = false;
            });
        }
    });
});
// 左右隐藏
function changeNav(){
    if($("#sdmsnav").hasClass("sdms_nav_spread")){
        $('#sdmsnav').removeClass('sdms_nav_spread');
    }else{
        $('#sdmsnav').addClass('sdms_nav_spread');
    }
    if($("#sdmsnav").hasClass("sdms_nav_hide")){
        $('#sdmsnav').removeClass('sdms_nav_hide');
    }else{
        $('#sdmsnav').addClass('sdms_nav_hide');
    }
}
function loginOut(userId,type){
    var index=layer.confirm('确定退出登陆吗？', {icon: 3, title:'提示'}, function(i){
        if(userId){
            var loginOutUrl='/Index/User/loginOut';
            var reloadUrl='/Index/User/login';
            if(type==1){
                loginOutUrl='/Admin/User/loginOut';
                reloadUrl='/Admin/User/login';
            }
            $.ajax({
                type : 'POST',
                url : loginOutUrl,
                dataType : 'json',
                data : {userId:userId},
                success : function(el){
                    var index=layer.open({
                        type:0,
                        icon:'1',
                        title:'成功提示信息：',
                        content:'退出登陆成功',
                        end: function(){
                            location.href=reloadUrl;
                        }
                    });
                    layer.style(index,{
                        width:'500px',
                        top:'120px'
                    });
                },
                error: function(el){
                    var index=layer.open({
                        type:0,
                        icon:'2',
                        title:'错误提示信息：',
                        content:'操作失败，请刷新重试',
                        end: function(){
                            location.reload();
                        }
                    });
                    layer.style(index,{
                        width:'400px',
                        top:'120px'
                    });
                }
            });
        }else{
            var index=layer.open({
                type:0,
                icon:'2',
                title:'错误提示信息：',
                content:'用户信息有误，请刷新重试',
                end: function(){
                    location.reload();
                }
            });
            layer.style(index,{
                width:'400px',
                top:'120px'
            });
        }
    });
    layer.style(index,{
        width:'500px',
        top:'120px'
    });
}
