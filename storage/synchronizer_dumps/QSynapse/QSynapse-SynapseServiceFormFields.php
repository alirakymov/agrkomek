<?php

return [
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);approved))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Article);approved)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Switcher',
        'label' => 'Публиковать',
        'placeholder' => '',
        'description' => '',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);content))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Article);content)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\BlockEditor',
        'label' => 'Содержимое',
        'placeholder' => 'Содержимое новости',
        'description' => 'основное содержимое новости',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);language))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Article);language)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Select',
        'label' => 'Язык',
        'placeholder' => 'Выберите язык',
        'description' => '',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);source))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Article);source)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Источник новости',
        'placeholder' => 'Ссылка на источник новости',
        'description' => 'укажите ссылку на первоисточник новости',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Article);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Article);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Заголовок новости',
        'placeholder' => 'Введите заголовок новости',
        'description' => 'Заголовок новости',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);Selection);QSynapse:SynapseAttributes())' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);ArticleForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects(QSynapse:SynapseRelations(QSynapse:Synapses(Article);articles;QSynapse:Synapses(ArticleType);type);QSynapse:SynapseServices(QSynapse:Synapses(Article);Manager);QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager))',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);Selection)',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes()',
        'type' => 2,
        'attributeFieldType' => null,
        'label' => 'Тип новости',
        'placeholder' => 'Выберите тип новости',
        'description' => 'раздел в которой будет опубликована новость',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleParser);link))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleParser);link)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Ссылка',
        'placeholder' => 'Укажите ссылку',
        'description' => 'Укажите ссылку источника',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleParser);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleParser);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Название',
        'placeholder' => 'Введите название источника',
        'description' => 'Название источника',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);Selection);QSynapse:SynapseAttributes())' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);ArticleParserForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects(QSynapse:SynapseRelations(QSynapse:Synapses(ArticleParser);sources;QSynapse:Synapses(ArticleType);type);QSynapse:SynapseServices(QSynapse:Synapses(ArticleParser);Manager);QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager))',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);Selection)',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes()',
        'type' => 2,
        'attributeFieldType' => null,
        'label' => 'Тип источника',
        'placeholder' => 'выберите тип источника',
        'description' => 'новости из данного источника будут привязаны к данном типу',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);ArticleTypeForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleType);language))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);ArticleTypeForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleType);language)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Select',
        'label' => 'Язык',
        'placeholder' => 'Выберите язык',
        'description' => '',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);ArticleTypeForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleType);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ArticleType);Manager);ArticleTypeForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ArticleType);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Название типа',
        'placeholder' => 'Введите название типа',
        'description' => 'название типа новости',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ConsultancyCategory);Manager);ConsultancyCategoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ConsultancyCategory);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ConsultancyCategory);Manager);ConsultancyCategoryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ConsultancyCategory);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Название категории',
        'placeholder' => 'Название категории',
        'description' => 'Название категории',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);approved))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);approved)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Switcher',
        'label' => 'Публиковать',
        'placeholder' => 'Публиковать',
        'description' => 'Публиковать',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);content))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);content)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\BlockEditor',
        'label' => 'Содержимое',
        'placeholder' => 'Содержимое',
        'description' => 'Содержимое',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);language))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);language)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Select',
        'label' => 'Язык',
        'placeholder' => 'Выберите язык',
        'description' => '',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Guide);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Заголовок',
        'placeholder' => 'Введите заголовок',
        'description' => 'заголовок справочной информации',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);SelectionField);QSynapse:SynapseAttributes())' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);GuideForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects(QSynapse:SynapseRelations(QSynapse:Synapses(Guide);guides;QSynapse:Synapses(GuideCategory);category);QSynapse:SynapseServices(QSynapse:Synapses(Guide);Manager);QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager))',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);SelectionField)',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes()',
        'type' => 2,
        'attributeFieldType' => null,
        'label' => 'Категория',
        'placeholder' => 'Укажите категорию',
        'description' => 'категория справочника',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);GuideCategoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(GuideCategory);language))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);GuideCategoryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(GuideCategory);language)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Select',
        'label' => 'Язык',
        'placeholder' => 'Выберите язык',
        'description' => '',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);GuideCategoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(GuideCategory);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(GuideCategory);Manager);GuideCategoryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(GuideCategory);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Название категории',
        'placeholder' => 'Введите название категории',
        'description' => 'название категории справочника',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager);MachineryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Machinery);content))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager);MachineryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Machinery);content)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\BlockEditor',
        'label' => 'Текст объявления',
        'placeholder' => 'Введите текст объявления',
        'description' => 'Введите текст объявления',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager);MachineryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Machinery);price))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager);MachineryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Machinery);price)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'price',
        'placeholder' => 'Введите стоимость техники',
        'description' => 'введите стоимость техники (в тенге)',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager);MachineryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Machinery);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Machinery);Manager);MachineryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Machinery);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Заголовок',
        'placeholder' => 'Введите заголовок',
        'description' => 'заголовок объявления',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Moderator);Manager);ModeratorForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Moderator);email))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Moderator);Manager);ModeratorForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Moderator);email)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Email',
        'placeholder' => 'Введите почту модератора',
        'description' => 'введите почту модератора',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Moderator);Manager);ModeratorForm);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);Selection);QSynapse:SynapseAttributes())' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Moderator);Manager);ModeratorForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects(QSynapse:SynapseRelations(QSynapse:Synapses(Moderator);moderators;QSynapse:Synapses(ModeratorRole);role);QSynapse:SynapseServices(QSynapse:Synapses(Moderator);Manager);QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager))',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);Selection)',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes()',
        'type' => 2,
        'attributeFieldType' => null,
        'label' => 'Роль модератора',
        'placeholder' => 'выберите роль модератора',
        'description' => 'выберите роль модератора',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);ModeratorPermission);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ModeratorPermission);component))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);ModeratorPermission)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ModeratorPermission);component)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Select',
        'label' => 'Компонент',
        'placeholder' => 'Выберите компонент',
        'description' => 'выберите компонент системы',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);ModeratorPermission);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ModeratorPermission);level))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);ModeratorPermission)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ModeratorPermission);level)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Select',
        'label' => 'Уровень доступа',
        'placeholder' => 'Выберите уровень доступа',
        'description' => 'укажите уровень доступа к данному компоненту системы',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);ModeratorPermission);QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);HiddenField);QSynapse:SynapseAttributes())' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);ModeratorPermission)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects(QSynapse:SynapseRelations(QSynapse:Synapses(ModeratorPermission);permissions;QSynapse:Synapses(ModeratorRole);role);QSynapse:SynapseServices(QSynapse:Synapses(ModeratorPermission);Manager);QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager))',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);HiddenField)',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes()',
        'type' => 2,
        'attributeFieldType' => null,
        'label' => 'Связь с ролью',
        'placeholder' => 'Связь с ролью',
        'description' => 'Связь с ролью',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);ModeratorRoleForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(ModeratorRole);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(ModeratorRole);Manager);ModeratorRoleForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(ModeratorRole);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Название роли',
        'placeholder' => 'введите название роли',
        'description' => 'название необходимо для более удобной идентификации',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);event))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);event)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Select',
        'label' => 'Событие',
        'placeholder' => 'Выберите событие',
        'description' => 'выберите событие из списка',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);messageKz))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);messageKz)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Textarea',
        'label' => 'Сообщение на казахском',
        'placeholder' => 'Введите сообщение на казахском',
        'description' => 'тело сообщения на казахском',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);messageRu))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);messageRu)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Textarea',
        'label' => 'Сообщение на русском',
        'placeholder' => 'Введите сообщение на русском',
        'description' => 'тело сообщения на русском',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);titleKz))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);titleKz)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Заголовок на казахском',
        'placeholder' => 'Введите заголовок на казахском',
        'description' => ' Заголовок сообщения на казахском',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);titleRu))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(NotificationMessage);Manager);NotificationMessage)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(NotificationMessage);titleRu)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Заголовок на русском',
        'placeholder' => 'Введите заголовок на русском',
        'description' => 'заголовок сообщения на русском',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Story);Manager);StoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Story);content))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Story);Manager);StoryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Story);content)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Textarea',
        'label' => 'Содержимое',
        'placeholder' => 'Содержимое',
        'description' => '',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Story);Manager);StoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Story);link))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Story);Manager);StoryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Story);link)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Ссылка',
        'placeholder' => 'Введите ссылки',
        'description' => '',
    ],
    'QSynapse:SynapseServiceFormFields(QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Story);Manager);StoryForm);QSynapse:SynapseServiceForms();QSynapse:SynapseAttributes(QSynapse:Synapses(Story);title))' => [
        'iSynapseServiceForm' => 'QSynapse:SynapseServiceForms(QSynapse:SynapseServices(QSynapse:Synapses(Story);Manager);StoryForm)',
        'iSynapseServiceSubject' => 'QSynapse:SynapseServiceSubjects()',
        'iSynapseServiceSubjectForm' => 'QSynapse:SynapseServiceForms()',
        'iSynapseAttribute' => 'QSynapse:SynapseAttributes(QSynapse:Synapses(Story);title)',
        'type' => 1,
        'attributeFieldType' => 'Qore\\Form\\Field\\Text',
        'label' => 'Заголовок',
        'placeholder' => 'Заголовок',
        'description' => 'Заголовок',
    ],
];