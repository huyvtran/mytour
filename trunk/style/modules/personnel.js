function addSalaryRow(a){
    $("<table><tr>"
        +"<td align='center'><input type='text' disabled class='x-text x-date' name='dateup[]' onclick='date_picker(this,{noTime:true})'></td>"//ngày
        +"<td align='center'><select class='x-select' name='skill_id[]'></select></td>"
        +"<td align='center'><select name='countb[]' disabled style='width:60px' class='x-select countb'></select></td>"//bac luong
        +"<td align='center' class='initsalary'></td>"
        +"<td align='center'><select disabled name='plussalary[]' class='x-select plusalary'>"
        +(function(){
            var t="";
            for(var i=0;i<=10;i++)
                t+="<option value='"+(i/10)+"'>"+(i/10)+"</option>";
            return t;
        })()
        +"</select></td>"//hệ số phụ
        +"<td class='sum_salary' align='center'></td>"//tong luong
        +"<td align='center'><a title='Xóa' onclick='$(this.parentNode.parentNode).remove()'><b>[ - ]</b></a></td>"
        +"</tr></table>")
    .find("tr")
    .afterTo('#salaryTb > tbody > tr:last-child')
    .each(function(){
        var elem = this;
        $(this)
        .find("select[name*=skill_id]")
        .empty("option")
        .each(function(){
            var sillsl = this;
            $("<option></option>")
            .appendTo(this);
            for( var i=0;i < SALARY_PARAMS.length;i++){
                $("<option value='"+SALARY_PARAMS[i].ID+"'>"+SALARY_PARAMS[i].title+"</option>")
                .each(function(){
                    this.data = SALARY_PARAMS[i];
                })
                .appendTo(this);
            }

            $(elem)
            .find("select.countb, select.plusalary")
            .onChange(function(){
                var data = sillsl.options[sillsl.selectedIndex].data;
                get_sum_salary( elem,data )
            });

        })
        .onChange(function(){
            var data = this.options[this.selectedIndex].data;
            if(!data){
                $(elem)
                .find("select.countb")
                .empty("option")
                .set('disabled',true);
                $(elem)
                .find("input.x-date")
                .set('disabled',true);
                $(elem)
                .find("select.plusalary")
                .set('disabled',true);
                $(elem)
                .find("td.sum_salary")
                .htm('');
                $(elem)
                .find("td.initsalary")
                .htm('');

                return;
            }
            $(elem)
            .find("select.countb")
            .empty("option")
            .set('disabled',false)
            .each(function(){
                for(var i=0;i < data.countb; i++ ){
                    $(this)
                    .append("<option value='"+(i+1)+"'>"+(i+1)+"</option>")
                }
            })
            $(elem)
            .find("input.x-date")
            .set('disabled',false);
            $(elem)
            .find("select.plusalary")
            .set('disabled',false);

            get_sum_salary( elem,data );
        });
    });
}

function get_sum_salary( elem,data ){
    //get sum salary
    var deg = parseFloat($(elem).find("select.countb").get('value'));
    var auxi = parseFloat($(elem).find("select.plusalary").get('value'));
    var param = parseFloat(data['salary']);
    var step = parseFloat(data['subsalary']);

    var sum = ( param + step*(deg-1) + auxi )*BASIC_SALARY;
    sum = Math.round(sum);
    var a = sum.toString().split(''),txt='';
    for(var i = 0 ; i < a.length ; i++ ){
        txt =((i % 3 == 2 ) ? ',' : '') + a[a.length-1 - i] + txt
    }

    $(elem)
    .find("td.sum_salary")
    .htm( txt );

    $(elem)
    .find("td.initsalary")
    .htm(data.salary);

}

function per_uptext(a){
    a.value = a.value.toString().toUpperCase();
}











function personnelAuditAttendance(month,year,obj,i){
    var f = arguments.callee, rel = obj.getAttribute('rel');
    if( i === undefined ) i = 0;

    obj.disabled = true;
    $.Ajax( baseURL +'/Personnel/Attendance/Audit',{
        type: 'POST',
        data:{
            month: month,
            year : year,
            count: i
        },
        success: function(js){
            var data;
            try{
                eval("data="+js);
            }catch(e){
                alert(js);
            }

            $(rel).htm('<div class="x-progress"><div class="x-progress-bar" style="width: '+data.progress+'%; "></div></div>');

            if( data.status == 'complete' ){
                obj.disabled = false;
                return;
            }
            f(month,year,obj,i+1)
        },
        error: function(){
            setTimeout(function(){
                f(month,year,obj,i+1);
            },5000);
        }
    })




}












