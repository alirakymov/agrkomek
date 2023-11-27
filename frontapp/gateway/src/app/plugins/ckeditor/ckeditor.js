import BalloonEditorBase from '@ckeditor/ckeditor5-editor-balloon/src/ballooneditor';

import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials';
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import Heading from '@ckeditor/ckeditor5-heading/src/heading';
import Indent from '@ckeditor/ckeditor5-indent/src/indent';
import IndentBlock from '@ckeditor/ckeditor5-indent/src/indentblock';
import Link from '@ckeditor/ckeditor5-link/src/link';
import List from '@ckeditor/ckeditor5-list/src/list';
import TodoList from '@ckeditor/ckeditor5-list/src/todolist';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice';
import Table from '@ckeditor/ckeditor5-table/src/table';
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar';
import TableProperties from '@ckeditor/ckeditor5-table/src/tableproperties';
import TableCellProperties from '@ckeditor/ckeditor5-table/src/tablecellproperties';
import TableColumnResize from '@ckeditor/ckeditor5-table/src/tablecolumnresize';
import TextTransformation from '@ckeditor/ckeditor5-typing/src/texttransformation';
import HeadingButtonsUI from '@ckeditor/ckeditor5-heading/src/headingbuttonsui';
import ParagraphButtonUI from '@ckeditor/ckeditor5-paragraph/src/paragraphbuttonui';
import Highlight from '@ckeditor/ckeditor5-highlight/src/highlight';
import CodeBlock from '@ckeditor/ckeditor5-code-block/src/codeblock';
import Image from '@ckeditor/ckeditor5-image/src/image';
import ImageToolbar from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageCaption from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStyle from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageResize from '@ckeditor/ckeditor5-image/src/imageresize';
import ImageUpload from '@ckeditor/ckeditor5-image/src/imageupload';
import LinkImage from '@ckeditor/ckeditor5-link/src/linkimage';
import SimpleUploadAdapter from '@ckeditor/ckeditor5-upload/src/adapters/simpleuploadadapter';


export default class BalloonEditor extends BalloonEditorBase {}

BalloonEditor.builtinPlugins = [
	Essentials,
	Autoformat,
	Bold,
	Italic,
	BlockQuote,
	Heading,
	Indent,
	IndentBlock,
	Link,
    Highlight,
	List,
    TodoList,
	Paragraph,
	PasteFromOffice,
	Table,
	TableToolbar,
    TableCellProperties,
    TableProperties,
    TableColumnResize,
	TextTransformation,
    HeadingButtonsUI,
    ParagraphButtonUI,
    CodeBlock,
    Image,
    ImageToolbar,
    ImageCaption,
    ImageStyle,
    ImageResize,
    ImageUpload,
    LinkImage,
    SimpleUploadAdapter,
];

BalloonEditor.defaultConfig = {
    highlight: {
        options: [
            { model: 'yellowMarker', class: 'marker-yellow', title: 'Yellow Marker', color: 'var(--ck-highlight-marker-yellow)', type: 'marker' },
            { model: 'greenMarker', class: 'marker-green', title: 'Green marker', color: 'var(--ck-highlight-marker-green)', type: 'marker' },
            { model: 'pinkMarker', class: 'marker-pink', title: 'Pink marker', color: 'var(--ck-highlight-marker-pink)', type: 'marker' },
            { model: 'blueMarker', class: 'marker-blue', title: 'Blue marker', color: 'var(--ck-highlight-marker-blue)', type: 'marker' },
            { model: 'redPen', class: 'pen-red', title: 'Red pen', color: 'var(--ck-highlight-pen-red)', type: 'pen' },
            { model: 'greenPen', class: 'pen-green', title: 'Green pen', color: 'var(--ck-highlight-pen-green)', type: 'pen' }
        ]
    },
    toolbar: {
        items: [
            'undo', 'redo', '|',
            'bold', 'italic', 'link', 'highlight', '|',
            'todoList','numberedList','|',
            'outdent', 'indent', '|',
            'codeBlock', 'blockQuote', 'insertTable', 'uploadImage', '|',
            'paragraph', 'heading1', 'heading2',
        ]
    },

    simpleUpload: {
        // The URL that the images are uploaded to.
        uploadUrl: '/~admin/gallery-image/editor-upload',

    },

    image: {
        toolbar: [
            'imageStyle:block',
            'imageStyle:inline',
            'imageStyle:side',
            '|',
            'imageStyle:alignLeft',
            'imageStyle:alignRight',
            '|',
            'imageStyle:alignBlockLeft',
            'imageStyle:alignCenter',
            'imageStyle:alignBlockRight',
            '|',
            'toggleImageCaption',
            'imageTextAlternative',
            '|',
            'linkImage'
        ]
    },

    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
        ]
    },

    table: {
        contentToolbar: [
            'tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties'
        ],
        // Configuration of the TableProperties plugin.
        tableProperties: {
            // ...
        },

        // Configuration of the TableCellProperties plugin.
        tableCellProperties: {
            // ...
        },
    },
};
