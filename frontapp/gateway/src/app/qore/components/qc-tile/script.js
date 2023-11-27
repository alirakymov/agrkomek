import { ProtocolInterface, CommandInterface } from '_scripts/qore/protocol.js';

import PhotoSwipeLightbox from '_scripts/plugins/photoswipe/js/lightbox/lightbox';
import PhotoSwipe from '_scripts/plugins/photoswipe/js/photoswipe';
import '_scripts/plugins/photoswipe/photoswipe.css';

import QtImage from './qt-contents/qt-image/component.vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import _ from 'lodash-es';

export default {
    mixins: [CommandInterface],
    components: {
        'qt-image': QtImage,
    },

    data: function() {
        return {
            replaceElements: ['tileData', 'breadcrumbs'],
            dispatchMap: [
                'title',
                'columns',
                'draggable',
                'breadcrumbs',
                'tileData',
                {key: 'reloadUrl', path: 'url'},
                {key: 'actions', path: 'component-actions'}
            ],
            tileData: _.get(this.options, 'tileData', []),
            breadcrumbs: _.get(this.options, 'breadcrumbs', []),
            inBlock: _.get(this.options, 'in-block', true),
            reloadUrl: _.get(this.options, 'url', null),
            draggable: _.get(this.options, 'sortable', false),
            title: _.get(this.options, 'component-title', null),
            actions: _.get(this.options, 'component-actions', {}),
            reordered: false,
            lightbox: null,
            cropper: null,
            croppingSizes: _.get(this.options, 'croppingSizes', []),
            croppingDropdown: null,
            croppingTile: null,
            croppingTileSizes: {},
            newCroppingSize: { w: null, h: null },
            activeCroppingSize: null,
            changedCroppingSizes: {},
        };
    },

    props: ['options'],

    mounted: function() {
        let $this = this;
        console.log(this.options);

        this.lightbox = new PhotoSwipeLightbox({
            gallery: '#' + this.id,
            children: 'a.photoswipe',
            pswpModule: PhotoSwipe
        });

        this.lightbox.on('uiRegister', function() {
            $this.lightbox.pswp.ui.registerElement({
                name: 'cropping',
                order: 6,
                isButton: false,
                html: '',
                onInit: (el) => {
                    el.classList.add('dropdown');
                    /** Create dropdown button */
                    let dropdownButton = document.createElement('button'), icon = document.createElement('i');
                    dropdownButton.classList.add('pswp__button', 'pswp__button--cropping', 'dropdown-toggle');
                    dropdownButton.setAttribute('data-bs-toggle', 'dropdown');
                    icon.classList.add('text-gray-lighter', 'far', 'fa-crop');
                    dropdownButton.appendChild(icon);
                    el.appendChild(dropdownButton);

                    /** Build cropping dropdown menu */
                    $this.croppingDropdown = document.createElement('div');
                    $this.croppingDropdown.classList.add('dropdown-menu', 'fs-sm');
                    $this.buildCroppingDropdown();
                    el.appendChild($this.croppingDropdown);

                    /** Disable cropper */
                    $this.cropper = null;
                },
                onClick: (event, el) => {
                }
            });
        });

        this.lightbox.init();
    },

    beforeDestroy: function() {
    },

    watch: {
        croppingSizes: function() {
            this.buildCroppingDropdown();
        }
    },

    computed: {
        tiles: {
            get: function() {
                return Array.isArray(this.tileData) ? this.tileData : Object.values(this.tileData);
            },
            set: function(tiles) {
                this.tileData = tiles;
            }
        },
        dragOptions: function() {
            return {
                disabled: this.draggable === false,
                animation:150
            };
        },
        id: function() {
            return this.name.replace(/[^a-z0-9]+/i, '-');
        }
    },

    methods: {

        isAction: function(action) {
            return typeof action === 'object' && action.actionUri !== undefined;
        },

        componentActionIcon: function(action) {
            return _.get(action, 'icon', 'fa fa-cog');
        },

        componentActionClick: function(action, e) {
            e.preventDefault();
            this.$axios.get(action.actionUri);
        },

        buildCroppingDropdown: function() {
            let $this = this;
            /** Clear */
            while (this.croppingDropdown.firstChild) {
                this.croppingDropdown.removeChild(this.croppingDropdown.firstChild);
            }

            /** Create dropdown menu items */
            for (let size of this.croppingSizes) {
                let item = document.createElement('a');
                item.classList.add('dropdown-item', 'rounded-1', 'm-0');
                item.innerText = size.w + 'x' + size.h;
                item.href="javascript:void(0)";
                item.onclick = function () {
                    $this.initializeCropperOptions(size);
                };
                this.croppingDropdown.appendChild(item);
            }

            /** New cropping size option */
            let item = document.createElement('a');
            item.classList.add('dropdown-item', 'rounded-1', 'm-0');
            item.innerText = 'Добавить';
            item.href="javascript:void(0)";
            item.onclick = function () {
                $this.addCroppingSize();
            };
            this.croppingDropdown.appendChild(item);

            /** Save button */
            item = document.createElement('a');
            item.classList.add('dropdown-item', 'rounded-1', 'm-0');
            item.innerText = 'Сохранить';
            item.href="javascript:void(0)";
            item.onclick = function () {
                $this.saveChangedCroppingSizes();
            };
            this.croppingDropdown.appendChild(item);
        },

        initializeCropperOptions: function(size) {
            let $this = this;
            this.croppingTile = this.getTile(this.lightbox.pswp.currSlide.data.element.getAttribute('data-tile-id'));
            this.lightbox.pswp.lockUI();

            if (this.cropper === null) {
                this.cropper = new Cropper(this.lightbox.pswp.currSlide.content.element, {
                    ready: function(event) {
                        $this.croppingTileSizes = $this.getCroppingSizesForActiveTile();
                        $this.cropper.setAspectRatio(size.w/size.h);
                        $this.cropper.setData($this.getCropBoxDataForSize(size));
                    },
                    cropend: function(event) {
                        $this.changedCroppingSizes[$this.activeCroppingSize.w + 'x' + $this.activeCroppingSize.h] = $this.cropper.getData();
                    }
                });
            } else {
                this.cropper.setAspectRatio(size.w/size.h);
                this.cropper.setData(this.getCropBoxDataForSize(size));
            }

            this.activeCroppingSize = size;
        },

        getCroppingSizesForActiveTile: function() {
            return {..._.get(this.croppingTile, 'data.__options.croppingSizes', {})};
        },

        getCropBoxDataForSize: function(size) {
            /** Search size in changed sizes */
            let sizeIndex = size.w + 'x' + size.h;
            if (this.changedCroppingSizes[sizeIndex]) {
                return this.changedCroppingSizes[sizeIndex];
            }
            /** Search size in tile sizes */
            let tileCroppingOptions = _.get(this.croppingTileSizes, sizeIndex, null);
            if (tileCroppingOptions !== null) {
                return tileCroppingOptions;
            }
            /** Return default */
            return { width: size.w, height: size.h };
        },

        normalizeCropBoxData: function(cropBoxData, direct = true) {
            let ratio = this.cropper.canvasData.naturalWidth/this.cropper.canvasData.width;
            let normalizedCropBoxData = {};
            for (let index in cropBoxData) {
                normalizedCropBoxData[index] = direct ? cropBoxData[index]*ratio : cropBoxData[index]/ratio;
            }
            return normalizedCropBoxData;
        },

        addCroppingSize: function() {
            let dialog = this.$protocol.findComponent(this.getNewCroppingSizeDialogName());
            dialog.show();
        },

        getNewCroppingSizeDialogName: function() {
            return this.name + '-NewCroppingSizeDialog';
        },

        getNewCroppingSizeDialogActions: function() {
            let $this = this;
            return [
                {
                    label: 'Отмена',
                    type: 'secondary',
                    action: 'close'
                },
                {
                    label: 'Добавить',
                    type: 'primary',
                    action: function() {
                        $this.addNewCroppingSize();
                    }
                },
            ];
        },

        addNewCroppingSize: function() {
            if (_.find(this.croppingSizes, this.newCroppingSize) === undefined) {
                this.croppingSizes.push({w: this.newCroppingSize.w, h: this.newCroppingSize.h});
            } else {
                this.getComponent('global-dialog').show({
                    message: {
                        title: 'Размер дублируется',
                        message: 'Данный размер уже присутствует в списке!',
                        type: 'warning',
                    },
                    actions: [
                        {
                            label: 'Ок',
                            action: function(dialog) {
                                dialog.close();
                            }
                        },
                    ],
                });
            }

            this.initializeCropperOptions(this.newCroppingSize);
        },

        saveChangedCroppingSizes: function() {
            let croppingRoute = _.get(this.croppingTile, 'routes.cropping', false);
            if (croppingRoute) {
                this.$axios.post(croppingRoute, {
                    'croppingSizes': this.changedCroppingSizes
                });
            }
        },

        fullImage: function(tile) {
            return _.get(tile, 'content.source.full-image', false);
        },

        thumb: function(tile) {
            return _.get(tile, 'content.source.thumb', '');
        },

        getOriginalWidth: function(tile) {
            return _.get(tile, 'data.__options.sizes.0', '');
        },

        getOriginalHeight: function(tile) {
            return _.get(tile, 'data.__options.sizes.1', '');
        },

        caption: function(tile) {
            return _.get(tile, 'data.name', '');
        },

        getTile: function(id) {
            for(let tile of this.tiles) {
                if (tile.id == id) {
                    return tile;
                }
            }
        },

        tileAction: function(action, event) {
            var $this = this;
            if (action.confirm !== undefined) {
                console.log(action);
                let dialog = this.$protocol.findComponent('global-dialog');
                if (dialog) {
                    dialog.show({
                        message: {
                            title: action.confirm.title,
                            message: action.confirm.message,
                            type: _.get(action.confirm, 'type', 'warning'),
                        },
                        actions: [
                            {label: 'Нет', type: 'secondary', action: 'close'},
                            {
                                label: 'Да, хочу',
                                action: function(dialog) {
                                    $this.$axios.get(action.actionUri);
                                    dialog.close();
                                }
                            },
                        ],
                    });
                }
            } else {
                this.$axios.get(action.actionUri);
            }
            event.preventDefault();
        },

        reload: function() {
            this.$axios.get(this.reloadUrl);
        },

        reorder: function(e) {
            if (this.draggable) {
                var order = [];
                _.each(this.tiles, function(tile){
                    order.push(tile.id);
                });

                this.$axios.post(this.draggable, {data: order}, {
                    emulateJSON: true
                });
            }
        }
    }
}
