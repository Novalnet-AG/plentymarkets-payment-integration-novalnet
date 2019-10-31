$(document).ready( function() {
    var current_date = new Date();      
    var max_year = current_date.getFullYear() - 18;
    var min_year = current_date.getFullYear() - 91;    
    var  userAgent = navigator.userAgent || navigator.vendor || window.opera;
    
    $("#nn_guarantee_date").on("keypress textInput",function (e)
    {
        var keyCode = e.which || e.originalEvent.data.charCodeAt(0);
        var expr = String.fromCharCode(keyCode);  
        if ( isNaN( expr ) || ( /android/i.test(userAgent) && $(this).val().length > 1 ) )
        {			
          e.preventDefault();
        }
            var day_val = $('#nn_guarantee_date').val();
            if( day_val.length == 1 ) {
                    if( (expr > -1 && day_val.charAt(0) > 3) || (expr == 0 && day_val.charAt(0) == 0) || (expr > 1 && day_val.charAt(0) == 3) )  {
                    return false;
                }
            }        
    });
    
    $('#nn_guarantee_date').on('blur', function() {
		var date, updated_date;
		updated_date = date = $('#nn_guarantee_date').val();
		if (date != '0' && date != '' && date.length < 2) {
			 updated_date = "0"+ date;         
		} else if (date == '0') {
			updated_date = date.replace('0', '01');        
		} 
		$('#nn_guarantee_date').val(updated_date);
    });      
    
    $("#nn_guarantee_year").on("input", function(e) {      
        var year_val = $(this).val();
        var year_len = year_val.length;
        let maximum_year = parseInt( max_year.toString().substring( 0 ,year_len ) );
        let minimum_year = parseInt( min_year.toString().substring( 0 ,year_len ) );        
        let user_val = year_val.substring( 0, year_len );               
        if( e.keyCode != 8 || e.keyCode != 46 ) {        
                    if( user_val > maximum_year || user_val <  minimum_year || isNaN(user_val) )  {        
                $(this).val( year_val.substring( 0, year_len - 1 ) );
                e.preventDefault();
              e.stopImmediatePropagation();
              return false;
          }  
        }
        
        });

    
    function yearAutocomplete(input_val, array_year) {
 
      var currentFocus;
  
      input_val.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
     
      closeAllLists();
      if (!val || val.length < 2) { return false;}
      currentFocus = -1;
      
      a = document.createElement("div");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      
      this.parentNode.appendChild(a);
      var count = 1;
      for (i = 0; i < array_year.length; i++) {     
        var regex = new RegExp( val, 'g' );
        if (array_year[i].match(regex)) {   
      if( count == 10 ) {
       break;
      }
          b = document.createElement("div");
          b.innerHTML = array_year[i].replace( val,"<strong>" + val + "</strong>" );          
          b.innerHTML += "<input type='hidden' class='year_active' value='" + array_year[i] + "'>";
          b.addEventListener("click", function(e) {
              input_val.value = this.getElementsByTagName("input")[0].value;
              closeAllLists();
          });
          a.appendChild(b);
      count++;
        }
      }
  });
  
      input_val.addEventListener("keydown", function(e) {
          var x = document.getElementById(this.id + "autocomplete-list");
          if (x) x = x.getElementsByTagName("div");
          if (e.keyCode == 40) {
            currentFocus++;            
            addActiveValue(x);
          } else if (e.keyCode == 38) { 
            currentFocus--;
            addActiveValue(x);
          } else if (e.keyCode == 13) {
            e.preventDefault();
            if (currentFocus > -1) {
              if (x) x[currentFocus].click();
            }
          }
      });
      function addActiveValue(x) {
        if (!x) return false;
        removeActiveValue(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        x[currentFocus].classList.add("autocomplete-active");
    var elements = $(x[currentFocus]);      
        $('#nn_guarantee_year').val( $('.year_active', elements).val() );
      }
      function removeActiveValue(x) {
        for (var i = 0; i < x.length; i++) {
          x[i].classList.remove("autocomplete-active");
        }
      }
      function closeAllLists(elmnt) {
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
          if (elmnt != x[i] && elmnt != input_val) {
            x[i].parentNode.removeChild(x[i]);
          }
        }
      }

      document.addEventListener("click", function (e) {
          closeAllLists(e.target);
      });
    }

    var year_range = [];
    
    for( var year = max_year; year >= min_year; year-- ) {              
        year_range.push('' + year + '');
    }

    yearAutocomplete(document.getElementById("nn_guarantee_year"), year_range);
    
    
    $('#novalnet_form').on('submit', function() {
    $('#novalnet_form_btn').attr('disabled',true);
        if ( $("#nn_guarantee_year").val() == '' || $("#nn_guarantee_date").val() == '' ) {
        alert($("#nn_dob_empty").val());
        $('#novalnet_form_btn').attr('disabled',false);
        return false;
        }
        
        if($("#nn_guarantee_month").val() == '0' ) {
        alert($("#nn_dob_invalid").val());
        $('#novalnet_form_btn').attr('disabled',false);
            return false;
        }
    
        return isActualDate($("#nn_guarantee_month").val(), $("#nn_guarantee_date").val(), $("#nn_guarantee_year").val());
        });
    
        function isActualDate (month, day, year) {
            var tempDate = new Date(year, --month, day);
            if( month !== tempDate.getMonth() || $("#nn_guarantee_year").val().length < 4) {
                alert($("#nn_dob_invalid").val());
                $('#novalnet_form_btn').attr('disabled',false);
                return false;
            }
            return true;
        }
    
});

 
