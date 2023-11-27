<template>
    <div class="w-100">
        <qc-block :options="{name: name + '-block', title: title, actions: actions, isBlock: inBlock, indents: inBlock}" @cdestroy="cdestroy()">
            <div class="w-100">
                <ol class="breadcrumb push-10">
                    <li v-for="(point, key, index) in breadcrumbs">
                        <a v-if="key != Object.keys(breadcrumbs).length - 1" class="link-effect" href="#" v-on:click="tableAction(point, $event)">{{ point.label }}</a>
                        <template v-else>{{ point.label }}</template>
                    </li>
                </ol>
            </div>
            <div :id="id">
                <draggable class="row g-sm items-push js-gallery push js-gallery-enabled"
                    v-model="tileData"
                    @change="reorder"
                    v-bind="dragOptions"
                    :no-transition-on-drag="false"
                    tag="div" >
                    <div class="col-md-4 col-lg-3 col-xl-2 animated fadeIn"
                         v-for="tile in tiles"
                         :key="name + '-' + tile.id"
                    >
                        <div class="options-container">
                            <img class="img-fluid options-item" :src="thumb(tile)" alt="">
                            <div class="options-overlay bg-black-75">
                                <div class="options-overlay-content">
                                    <h3 class="h4 fw-normal text-white mb-1">{{ caption(tile) }}</h3>
                                    <div class="btn-group" role="group" data-toggle="buttons">
                                        <a class="btn btn-sm btn-secondary photoswipe" :data-tile-id="tile.id" :data-pswp-width="getOriginalWidth(tile)" :data-pswp-height="getOriginalHeight(tile)" :href="fullImage(tile)" :title="caption(tile)"><i class="fa fa-search-plus"></i></a>
                                        <a class="btn btn-sm btn-secondary" v-for="action in tile.actions" :disabled="action.actionUri ? false : true" v-on:click="tileAction(action, $event)" :title="action.label"><i v-if="action.icon" :class="action.icon"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </draggable>
            </div>
        </qc-block>
        <qc-dialog :options="{name: getNewCroppingSizeDialogName(), actions: getNewCroppingSizeDialogActions()}">
            <div class="row p-2 m-0">
                <div class="col p-0 me-2">
                    <div class="form-floating">
                        <input type="text" class="form-control" placeholder="Ширина" v-model="newCroppingSize.w">
                        <label for="example-text-input-floating">Ширина</label>
                    </div>
                </div>
                <div class="col p-0">
                    <div class="form-floating">
                        <input type="text" class="form-control" placeholder="Высота" v-model="newCroppingSize.h">
                        <label for="example-text-input-floating">Высота</label>
                    </div>
                </div>
            </div>
        </qc-dialog>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
