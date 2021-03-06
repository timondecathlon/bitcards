
//получаем шаблоны
let tmpl_card = document.getElementById('tmpl-card').innerHTML;
let tmpl_column = document.getElementById('tmpl-column').innerHTML;
let tmpl_board = document.getElementById('tmpl-board').innerHTML;
let tmpl_desc = document.getElementById('tmpl-card-description').innerHTML;
let tmpl_tag = document.getElementById('tmpl-card-tag').innerHTML;
let tmpl_form= document.getElementById('tmpl-form').innerHTML;
let tmpl_form_tag= document.getElementById('tmpl-form-tag').innerHTML;

renderBoards();

function renderBoards(){
    let params = [];

    params.push('user_id='+getCookie('user_id'));
    params.push('user_hash='+getCookie('user_hash'));


    let data = sendAjaxRequestGet('http://cards.bitkit.pro/api/v1.0/users/getcards/index.php',params,false);
    //console.log(data);

    //раскодирую JSON
    let data_arr = JSON.parse(data);
    //console.log(data_arr);

    //подготавливаю массивы для вкладок и контента
    let tabs = [];
    let contents = [];

    //заполняю массивы вкладок и контента
    for(key in data_arr){
        tabs.push(key);
        contents.push(data_arr[key]);
    }

    //console.log(tabs);
    //console.log(contents);

    //отрисовываю html табов
    let text_tabs = '';
    //получаю шаблон вкладки доски
    let tmpl_tab = document.getElementById('tmpl-tab').innerHTML;

    for(let i = 0; i < tabs.length; i++){
        //заменяю плейсходер на переменную и дописываю
        text_tabs += tmpl_tab.replace('${var}',tabs[i]);
    }
    document.getElementById('tabs').innerHTML = text_tabs;


    //отрисовываем контент вкладок
    let tabs_content = '';


    //для всех контентов досок
    for(let i = 0; i < contents.length; i++){
        let columns_text = '';
        //собираю html колонок
        for(let column in contents[i]){
            let tasks_text = '';
            //собираю html все задач для колонки
            for(let j =0; j < contents[i][column].length; j++){
                let card_html = getCardHTML(contents[i][column][j]);

                tasks_text += card_html;
            }
            //наполняю html колонок

            //заменяю и дописываю колонку с задачами
            columns_text += tmpl_column.replaceAll('${column}',column)
                .replace('${tasks_text}',tasks_text);
        }

        let add_content = document.getElementById('tmpl-column-add').innerHTML;

        //наполняю html доски

        //заменяю плейсхолдеры и дописываю колнки на доску
        tabs_content += tmpl_board.replace('${columns_text}',columns_text).replace('${add_content}',add_content).replace('${board_name}',tabs[i]);
    }

    document.getElementById('tabs-content').innerHTML = tabs_content;
    tabsInitialize('body');

    let sort = ['.tasksSortable','.columnsSortable'];
    makeThemSortable(sort);
}

function logOut(){
    let elem = event.target.closest('.btn-logout');
    if(elem){
        let params = [];

        params.push('user_id='+getCookie('user_id'));
        params.push('user_hash='+getCookie('user_hash'));

        let response = sendAjaxRequestGet('http://cards.bitkit.pro/api/v1.0/users/logout/index.php',params,false);

        let data = JSON.parse(response);

        if(data.result === true ){
            deleteCookie('user_id');
            deleteCookie('user_hash ');
            routing();
        }
    }
}


function modalOpen(){
    let elem = event.target.closest('.card-title');
    if (elem) {
        document.getElementById('modal-kit').style.display = 'flex';

        //получаем id карточки
        let id = elem.closest('form').querySelector('input').value;


        //получаем все  данные по карточке
        let params = [];
        params.push('id='+id);
        let info = JSON.parse(sendAjaxRequestPost('http://cards.bitkit.pro/api/v1.0/cards/get/index.php',params,false));
        console.log(info);

        let card_html = '';

        //проставляем в шаблон
        card_html = tmpl_form.replaceAll('${id}',info.id)
            .replace('${title}',info.title)
            .replace('${description}',info.description);

        //если есть теги
        if(tags = info.tags){
            //набор тэгов
            let tags_html = '';
            for(let t=0; t < tags.length; t++){
                tags_html += tmpl_form_tag.replace('${id}',tags[t].id)
                    .replace('${color}',tags[t].color)
                    .replace('${title}',tags[t].title);
            }
            console.log(tags_html);
            card_html = card_html.replace('${tags}',tags_html);
        }

        //если не пригодилось убираем
        card_html = card_html.replace('${description}','');
        card_html = card_html.replace('${tags}','');

        document.getElementById('modal-content').innerHTML = card_html;


        //нужно находить внутри модалки кнопки мето, бежать по ним и если такое есть в инфо по тэгам есть то давать аттрибут

        //показываем все содержимое полей
        let textboxes = document.getElementById('modal-kit').getElementsByTagName('textarea');
        for(let i = 0; i < textboxes.length; i++){
            if(textboxes[i].style.height < textboxes[i].scrollHeight){
                textboxes[i].style.height = textboxes[i].scrollHeight + "px";
            }
        }
    }
}

function tagsPoolToggle(){
    let pool = document.getElementById('tags-pool');
    if(getComputedStyle(pool).display === 'none'){
        pool.style.display = 'flex';
    }else{
        pool.style.display = 'none';
    }
}

function tagsPoolToggleClick(){
    let elem = event.target.closest('.tags-pool-toggle');
    if (elem) {
        tagsPoolToggle();
    }
}



function tagAddClick(){
    let elem = event.target.closest('.tag-source');
    if (elem) {

        if(!document.querySelector('.tags-selected').querySelector('[value="'+elem.querySelector('input').value+'"]')){
            //клонируем тэг
            let new_elem = elem.cloneNode(true);
            //убираем класс
            new_elem.classList.remove('tag-source');
            //удаляем дизабельность
            new_elem.querySelector('input').removeAttribute('disabled');
            //добавляю в пул выбранных
            elem.closest('.form-tags-area').querySelector('.tags-selected').innerHTML += new_elem.outerHTML;
        }

    }
}

function modalCloseClick(){
    let elem = event.target.closest('.modal-close');
    if (elem) {
        modalSave();
        modalClose();
    }
}

function modalTagDelete(){
    let elem = event.target.closest('.tag-delete');
    if (elem) {
        elem.closest('.form-tag').remove();
    }
}

function modalClose(){
    document.getElementById('modal-kit').style.display = 'none';
}

function modalSave() {
    let form = document.getElementById('modal-kit').querySelector('form');

    let response =  sendAjaxRequest('POST',getFormUrl(form),getFormElements(form), false);
    let card_result = JSON.parse(response);

    //1) берем id из ответа update.php
    let id = card_result.id;

    //2) получаем всю инфу по карточке методом get.php
    let params = [];
    params.push('id='+id);
    let card_data = JSON.parse(sendAjaxRequestPost('http://cards.bitkit.pro/api/v1.0/cards/get/index.php',params,false));

    //3) считываем шаблон карточки и заменяем данные в нем
    let card_html = getCardHTML(card_data);

    //4) удаляем старую верстку карточки и заменяем на новую + убираем рендер боардс
    document.querySelector('[data-element-id="'+id+'"]').outerHTML = card_html;

}



function cardColumnAdd(){
    let elem = event.target.closest('.task-column-add');
    if (elem) {
        let column = document.getElementById('tmpl-column').innerHTML;
        //вырезаем плейсхолдеры
        let column_new = column.replaceAll('${column}','Новая')
            .replace('${tasks_text}','');
        //дорисовываем на доску
        elem.closest('.tab-content').querySelector('.grid-columns').innerHTML += column_new;

        //сохраняем структуру
        gridElementsSave()
    }
}


function boardDelete(){
    let elem = event.target.closest('.board-delete');
    if (elem) {
        if(confirm('Вы уверены')){
            document.querySelector('.tab-active').remove();
            document.querySelector('.tab-content-active').remove();
        }
    }
}

function cardAdd(){
    let elem = event.target.closest('.card-add');
    if (elem) {
        if(document.querySelector('.task-new-container')){
            document.querySelector('.task-new-container').remove()
        }
        let card_add = document.getElementById('tmpl-card-add').innerHTML;
        elem.closest('.task-column').querySelector('.task-column-body').innerHTML += card_add;
        elem.closest('.task-column').querySelector('.task-column-body').scrollTop = elem.closest('.task-column').querySelector('.task-column-body').scrollHeight;

    }
}

function cardDelete(){
    let elem = event.target.closest('.card-delete');
    if (elem) {
        elem.closest('.task-new-container').remove();
    }
}

function cardRemove(){
    let elem = event.target.closest('.card-remove');
    if (elem) {
        if(confirm('Вы ьуверены что хотите удалить карточку?')){
            let id = elem.getAttribute('data-id');
            console.log(id);
            document.querySelector('[data-element-id="'+id+'"]').remove();

            let params = [];
            params.push('id='+id);
            sendAjaxRequestPost('http://cards.bitkit.pro/api/v1.0/cards/delete/index.php',params,false);
            gridElementsSave();
            modalClose();

        }

    }
}


function boardSelect() {
    let elem = event.target.closest('.tab');
    if (elem) {
        tabSelect();
    }
}

function boardAdd() {
    let elem = event.target.closest('.board-add');
    if (elem) {
        let tab = document.getElementById('tmpl-tab').innerHTML;
        let tab_new = tab.replace('${var}','Новая доска');
        //дописываю новую вкладку
        elem.closest('.container-main').querySelector('.tabs-block').querySelector('.tabs').innerHTML += tab_new;

        //шаблон добавления колонки
        let add = document.getElementById('tmpl-column-add').innerHTML;

        //новая колонка
        let column = document.getElementById('tmpl-column').innerHTML;
        let column_new = column.replaceAll('${column}','Новая').replace('${tasks_text}','');

        //новая доска
        let board = document.getElementById('tmpl-board').innerHTML;
        let board_new = board.replace('${columns_text}',column_new).replace('${add_content}',add);

        //дописываю новую доску
        document.querySelector('.tabs-block').querySelector('.tabs-content').innerHTML += board_new;
    }
}


function cardCreate() {
    let elem = event.target.closest('.btn-ajax-card');
    if (elem) {
        if(elem.closest('form').querySelector('textarea').value.trim() !== ''){
            let params = getFormElements();
            params.push('user_id='+getCookie('user_id'));
            params.push('user_hash='+getCookie('user_hash'));

            let response =  sendAjaxRequestGet(getFormUrl(),params, false);
            //alert(response);

            let data_arr = JSON.parse(response);
            let post_id = data_arr['id'];
            let post_title = data_arr['title'];

            //заменяю плейсхолдеры на переменные и дописываю
            let tmp = document.getElementById('tmpl-card').innerHTML;
            let task = tmp.replaceAll('${var.id}',post_id)
                .replaceAll('${var.title}',post_title);


            //очищаем тэги и описалово - их со старту быть не может
            task = task.replace('${description}','');
            task = task.replace('${tags}','');

            let column = elem.closest('.task-column').querySelector('.task-column-body');
            elem.closest('.task-new-container').remove();

            column.closest('.task-column').querySelector('.task-column-body').innerHTML += task;

            //тут косяк срабатывает но не сохраняет
            gridElementsSave();
        }
    }
}

function getCardHTML(data){
    let card_html = '';
    //заменяю плейсхолдеры на переменные и дописываю
    card_html = tmpl_card.replaceAll('${var.id}',data.id)
        .replaceAll('${var.title}',data.title);

    //если есть описание
    if(data.description){
        card_html = card_html.replace('${description}',tmpl_desc)
    }

    //если есть теги
    if(tags = data.tags){
        //набор тэгов
        let tags_html = '';
        for(let t=0; t < tags.length; t++){
            tags_html += tmpl_tag.replace('${color}',tags[t].color)
                .replace('${title}',tags[t].title);
        }
        card_html = card_html.replace('${tags}',tags_html);
    }

    //если не пригодилось убираем
    card_html = card_html.replace('${description}','');
    card_html = card_html.replace('${tags}','');

    return card_html;
}



function cardChange() {
    let elem = event.target.closest('.card-moderate');
    if (elem) {
        elem.closest('.card-title').querySelector('textarea').removeAttribute('disabled');
        elem.closest('.card-title').querySelector('textarea').style.display = 'block';
        elem.closest('.card-title').querySelector('div').style.display = 'none';

        elem.closest('.card-title').querySelector('textarea').focus();
        elem.closest('.card-title').querySelector('textarea').style.height = this.closest('.card-title').querySelector('textarea').scrollHeight + "px";
    }
}

function boardsSave() {
    let elem = event.target.closest('.grid-elements-save');
    if (elem) {
        gridElementsSave();
    }
}


function boardsPanel() {
    let elem = event.target.closest('#show-panel');
    if (elem) {
        let margin_left = getComputedStyle(document.querySelector('.boards-list')).marginLeft;
        if(margin_left === '-240px'){
            document.querySelector('.boards-list').style.marginLeft = '0px';
        }else{
            document.querySelector('.boards-list').style.marginLeft = '-240px';
        }

    }
}

function menuPanel() {
    let elem = event.target.closest('.toggle-menu');
    if (elem) {
        let margin_right = getComputedStyle(document.querySelector('.menu')).marginRight;
        if(margin_right === '-350px'){
            document.querySelector('.menu').style.marginRight = '0px';
        }else{
            document.querySelector('.menu').style.marginRight = '-350px';
        }
    }
}



document.onclick = function() {
    modalCloseClick();

    cardColumnAdd();
    cardAdd();
    cardDelete();
    cardRemove();
    boardDelete();
    boardSelect();
    boardAdd();
    cardCreate();
    cardChange();
    boardsPanel();
    menuPanel();

    tagsPoolToggleClick();
    tagAddClick();

    modalTagDelete();

    logOut();
};

document.ondblclick = function() {
    modalOpen();
};




//сохранение всего при изменении названия доски
$('body').on('change','.board-title, .column-title',function(){
    if(this.value !== '') {
        gridElementsSave();
    }
});



//обновление карточки
$('body').on('change','.task-title-edit',function(){
    if(this.value !== ''){
        sendAjaxRequest('POST',getFormUrl(),getFormElements(), false);
        //console.log(sendAjaxRequest('POST',getFormUrl(),getFormElements(), false));
        this.closest('.card-title').querySelector('textarea').setAttribute('disabled','true');

        this.closest('.card-title').querySelector('textarea').style.display = 'none';
        this.closest('.card-title').querySelector('div').style.display = 'block';

        //alert(this.value);

        this.closest('.card-title').querySelector('div').innerHTML = this.value;
    }

});

//обновление карточки
$('body').on('change','input',function(){
    //$('#grid-tasks').find('.grid-elements-save').click();
});


$('body').on('keyup','textarea',function(){
    if(this.scrollTop > 0){
        this.style.height = this.scrollHeight + "px";
    }
});

