$.validator.addMethod('validPassword',
function(value, element, param){
    if (value != ''){
        if(value.match(/.*[a-z]+.*/i)==null){
            return false;
        }
        if(value.match(/.*\d+.*/)==null){
            return false;
        }
    }
    return true;
},
'Hasło musi zawierać przynajmniej jedną literę i cyfrę'
);

$.validator.addMethod('validLogin',
function(value, element, param){
    if (value != ''){
        if (!value.match(/^[a-z\d]+$/i)){
            return false;
        }
        if(value.match(/.*-+.*/)){
            return false;
        }
    }
    return true;
},
'Login może składać się tylko z liter i cyfr bez polskich znaków.'
);

  