//делаем все первые вкладки активными
function tabsInitialize(elem){
    let tabs = document.querySelector(elem).getElementsByClassName('tabs-block');
    for(let i = 0; i < tabs.length; i++){
        if(!tabs[i].querySelector('.tab').classList.contains('offer-tab')){
            tabs[i].querySelector('.tab').classList.add('tab-active');
            tabs[i].querySelector('.tab-content').classList.add('tab-content-active');
        }
    }
}


/*
 КОД ДЛЯ ПЕРЕКЛЮЧЕНИЯ ВКЛАДОК
 */
function tabSelect(){
    let elem = event.target.closest('.tab');
    if (elem) {
        let tabs = elem.closest('.tabs').querySelectorAll('.tab');
        let tabs_num = tabs.length;
        //console.log(tabs_num);
        for(let t = 0 ; t < tabs_num; t++){
            if(tabs[t] === elem){
                cr = t;
            }
        }
        let tabs_content = elem.closest('.tabs-block').querySelector('.tabs-content').children;


        for(let c = 0 ; c < tabs_num; c++){
            tabs[c].classList.remove('tab-active');
            tabs_content[c].classList.remove('tab-content-active');
        }

        tabs_content[cr].classList.add('tab-content-active');
        tabs[cr].classList.add('tab-active');

    }
}



function makeThemSortable(selectors) {
    //делаем сортбельными
    for(let i =0; i < selectors.length; i ++){
        $(selectors[i]).sortable('enable');
        $(selectors[i]).sortable({
            connectWith: selectors[i],
            cursor: "move",
            update: function() {
                //alert('fg');
                gridElementsSave();
            }
        });
    }
}

function makeThemNotSortable(selectors) {
    //делаем сортбельными
    for(let i =0; i < selectors.length; i ++){
        $(selectors[i]).sortable('disable');
    }
}

function getFormUrl(form = null) {
    if(!form){
        form = event.target.closest('form');
    }
    return form.action;
}

function disableForm() {
    event.preventDefault();
}

function getFormElements(form = null) {

    let params = [];
    if(!form){
        form = event.target.closest('form')
    }
    //let form = event.target.closest('form');
    for(let i = 0; i < form.elements.length; i++){
        if(form.elements[i].name && form.elements[i].disabled !== true ){
            console.log(form.elements[i].name);
            if((form.elements[i].type === 'checkbox' || form.elements[i].type === 'radio') && !form.elements[i].checked) continue;
            params.push(form.elements[i].name + '=' + form.elements[i].value);
        }
    }
    params.push('ajax=1');
    return params;
}

function saveFormAsync() {
    sendAjaxRequestPost(getFormUrl(),getFormElements(), false);
}

function sendFormAsync() {
    disableForm();
    saveFormAsync();
}


function sendAjaxRequest(type,url,params,async){
    let xhr = new XMLHttpRequest();
    xhr.open(type, url, async);

    if(type == 'POST'){
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    }

    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            //alert(xhr.responseText + 'ответ от файла');
            return xhr.responseText;
        }
    };

    if(type == 'POST') {
        xhr.send(params.join('&'));
    }else{
        url += '?' + params.join('&');
        xhr.send(null);
    }


    return xhr.responseText;
    //!!!!!!!!если async TRUE то не возвращает значение (не успевает)

    //return xhr.responseText;


}


function sendAjaxRequestPost(url,params,async){
    let xhr = new XMLHttpRequest();
    xhr.open('POST', url, async);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            //alert(xhr.responseText + 'ответ от файла');
        }
    };
    xhr.send(params.join('&'));

    //!!!!!!!!если async TRUE то не возвращает значение (не успевает)
    return xhr.responseText;

}


function sendAjaxRequestGet(url,params,async){
    let xhr = new XMLHttpRequest();
    url += '?' + params.join('&');
    xhr.open('GET', url, async);
    //xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if(xhr.readyState === 4 && xhr.status === 200){
            //alert(xhr.responseText + 'ответ от файла');
        }
    };


    xhr.send(null);
    //xhr.send(params.join('&'));

    //!!!!!!!!если async TRUE то не возвращает значение (не успевает)
    return xhr.responseText;

}



function add_script(src){
    let fileRef=document.createElement("script");
    fileRef.type="text/javascript";
    fileRef.src=src;
    document.getElementsByTagName("head")[0].appendChild(fileRef);
}


function getPostHtml(url, post_id, table_id){
    let params = [];
    params.push('post_id='+post_id);
    params.push('table='+table_id);
    return (sendAjaxRequest('POST',url,params,false));
}


function gridElementsSave(){

    //let elem = event.target.closest('.grid-elements-save');
    let elem = 1;

    if (elem) {

        let col_str = '';
        let boards_arr_content = [];        //массив  с контентом досок
        let boards_arr_names = [];        //массив с именами досок

        let book_sum_arr = {};

        //Собираем имена форм
        let boards_names = document.getElementById('tabs').getElementsByClassName("tab");
        //alert(boards_names.length);
        for (let n = 0; n < boards_names.length; n++) {
            boards_arr_names.push(boards_names[n].querySelector('input').value);
        }

        //alert(boards_arr_names);

        //Собираем контент книг
        let board_content = document.querySelector('.tabs-content').getElementsByClassName("tab-content");
        //Для каждой КНИГИ собираем контент
        for (let c = 0; c < board_content.length; c++) {

                //массив колонок
                let cols_arr_full = {};

                //Для всех КОЛОНОК внутри доски
                let cols = board_content[c].getElementsByClassName("grid-column");
                for (let i = 0; i < cols.length; i++) {
                    //пустой массив элементов внутри колонки
                    let els_arr = [];

                    //получаем ширину колонки
                    let column_attribute;
                    column_attribute = cols[i].querySelector('.task-column-title').querySelector('input').value;

                    //alert(column_attribute);

                    //Для всех БЛОКОВ внутри КОЛОНКИ
                    //let elements = cols[i].getElementsByClassName("task-column-element");
                    let elements = cols[i].getElementsByClassName("grid-element-unit");
                    //добавляем id элемента в массив в цикле
                    for (let j = 0; j < elements.length; j++) {
                        els_arr.push(elements[j].getAttribute('data-element-id'));
                    }

                    cols_arr_full[column_attribute] = els_arr;
                }
                boards_arr_content.push(cols_arr_full) ;

        }

        //alert(boards_arr_content);

        for(let res = 0; res < boards_arr_names.length; res++ ){
            book_sum_arr[boards_arr_names[res]] = boards_arr_content[res];
        }

        let books_str = JSON.stringify(book_sum_arr);
        console.log(books_str);

        let url = window.location.protocol+'//'+document.domain+'/api/v1.0/users/savecards/index.php';
        let user_id = getCookie('user_id');
        let user_hash = getCookie('user_hash');
        let params = [];
        params.push('user_id='+user_id);
        params.push('user_hash='+user_hash);
        params.push('elements='+books_str);

        //alert(sendAjaxRequestPost(url,params,false));

        sendAjaxRequestGet(url,params,false);

    }
}


String.prototype.replaceAll = function(search, replacement) {
    let target = this;
    return target.replace(new RegExp(escapeRegExp(search), 'g'), replacement);
};

function escapeRegExp(str) {
    return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}


function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}


/*
function setCookie(name, value, options = {}) {

    options = {
        path: '/',
        // при необходимости добавьте другие значения по умолчанию
        ...options
    };

    if (options.expires instanceof Date) {
        options.expires = options.expires.toUTCString();
    }

    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

    for (let optionKey in options) {
        updatedCookie += "; " + optionKey;
        let optionValue = options[optionKey];
        if (optionValue !== true) {
            updatedCookie += "=" + optionValue;
        }
    }

    document.cookie = updatedCookie;
}
*/
function setCookie(name, value){
    document.cookie = name+"="+value+';path=/;domain=cards.bitkit.pro';
}

function deleteCookie(name) {
    setCookie(name, "", {
        'max-age': -1
    })
}

function redirect(url){
    window.location.href = url;
}

function routing() {
    let params = [];
    params.push('user_id='+getCookie('user_id'));
    params.push('user_hash='+getCookie('user_hash'));

    let response = sendAjaxRequestGet('http://cards.bitkit.pro/api/v1.0/users/logcheck/index.php',params,false);
    //console.log(data);

    //раскодирую JSON
    let data = JSON.parse(response);
    console.log(data);
    if(data.result === true && window.location.href != 'http://cards.bitkit.pro/'){
        redirect('http://cards.bitkit.pro/');
    }
    if(data.result === false && window.location.href != 'http://cards.bitkit.pro/auth/'){
        redirect('http://cards.bitkit.pro/auth/');
    }

}


/**
 * СОХРАНЕНИЕ КНИГ СТРАНИЦ КОЛОНОК И ПОЛЕЙ



 */
