$(document).ready(function() {
    console.log( "ready!" );
    $('#add').click(function(){
        if (typeof(html)=='undefined') {
            html = '';
            addCount1=1;
        }
        html = '<tr id="addIns'+addCount1+'"><td><input type=text name=additionalKey[] placeholder=Label ></td>\
        <td>&nbsp;<input type=text name=additionalValue[] placeholder=Value></td>\
        <td>&nbsp;<button type="button"  class="btn-danger my-1" id="removeAdd" data-id="Ins'+addCount1+'">\
        <i class="fa-solid fa-trash-can"></i></button></td></tr> <br/></tr>'
        $('#additional').append(html);
        addCount1 +=1;
    });
    $('#var').click(function(){
        if (typeof(html2)=='undefined') {
            html2 = '';
            addCount2=1;
        }
        html2 = '<tr id="varIns'+addCount2+'" ><td><input type=text name=variationKey[] placeholder=Label ></td>\
        <td>&nbsp;<input type=text name=variationValue[] placeholder=Value></td>\
        <td>&nbsp;<input type=text name=variationPrice[] placeholder=Price></td>\
        <td>&nbsp;<button type="button"  class="btn-danger my-1" id="removeVar" data-id="Ins'+addCount2+'">\
        <i class="fa-solid fa-trash-can"></i></button></td></tr> <br/>'
        $('#variations').append(html2);
        addCount2 +=1;
    });


    $('body').on('click', '#removeAdd', function(){
        console.log('delete Additional fields');
        id = $(this).data('id');
        $("#add"+id).remove();
    });

    $('body').on('click', '#removeVar', function(){
        console.log('delete variations');
        id = $(this).data('id');
        $("#var"+id).remove();
    });

    $('body').on('click', '.view', function(){
        id = $(this).data('id');
        viewProduct(id);
    });
    
});

function viewProduct(id)
{
    $.ajax({
        method: "POST",
        url: "/product/getInfo",
        data: {'id': id}
    }).done(function( data ) {
        console.log('ret '+data);
        data = JSON.parse(data);
        
        console.log(data);
        modalHead = 'Additional fields of <b>'+data.name+'</b>';
        modalBody =  '<table>';
        $("#myModal").modal('show');
        
        if (data.Additional !='null') {
            for(var i in data.additional_fields) {
            modalBody +="<tr><td class='px-2'><b>\
            "+i+"</b></td><td class='px-2'>"+data.additional_fields[i]+"</td></tr>";
            } 
        }
        modalBody += '</table>'
        console.log(modalBody);
        $('.modal-body').html(modalBody);
        $('.modal-title').html(modalHead);
        
    });
}