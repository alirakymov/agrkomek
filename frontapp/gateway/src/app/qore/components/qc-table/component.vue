<template>
    <div class="w-100">
        <qc-block :options="{name: name + '-block', title: title, actions: actions, isBlock: inBlock, indents: inBlock}" @cdestroy="cdestroy()" ref="children">
            <div v-if="Object.keys(breadcrumbs).length" class="breadcrumb pb-3 w-100">
                <ol class="breadcrumb">
                    <li v-for="(point, key, index) in breadcrumbs" class="breadcrumb-item">
                        <a v-if="key != Object.keys(breadcrumbs).length - 1" class="link-fx" href="#" v-on:click="tableAction(point, $event)" v-html="point.label"></a>
                        <template v-else><span v-html="point.label"/></template>
                    </li>
                </ol>
            </div>
            <component v-for="component in components"
                :key="component.id"
                :is="component.type"
                :options="component.data"
            ></component>
            <div class="table-responsive" v-if="reports.length">
                <table class="table table-bordered table-dashed table-hover">
                    <thead>
                        <tr>
                            <th class="text-center col-1">#</th>
                            <th class="text-center col">Прогресс</th>
                            <th class="text-center col-1">Всего</th>
                            <th class="text-center col-1">Обработано</th>
                            <th class="text-center col-1">...</th>
                        </tr>
                    </thead>
                    <draggable v-model="data" tag="tbody" @change="reorder" v-bind="dragOptions" :no-transition-on-drag="false">
                        <tr v-for="(row, id) in reports" :key="'row-' + id">
                            <td class="text-center col-1">{{ row.id }}</td>
                            <td class="text-center col align-middle">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" :style="{width: (row.processed/row.counted * 100) + '%'}" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                            <td class="text-center col-1">{{ row.counted }}</td>
                            <td class="text-center col-1">{{ row.processed }}</td>
                            <td class="text-center col-1">
                                <div class="btn-group" role="group" data-toggle="buttons">
                                    <a :href="row.routes.download" class="btn btn-sm btn-alt-secondary" type="button" data-toggle="click-ripple" title="Редактировать"><i class="fas fa-download"></i></a>
                                    <button v-on:click="tableAction({actionUri: row.routes.remove, confirm: {title: 'Удаляем отчет?', message: 'Вы действительно хотите удалить данный отчет?'}}, $event)" class="btn btn-sm btn-alt-secondary" type="button" data-toggle="click-ripple" title="Удалить"><i class="fa fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    </draggable>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-dashed table-hover">
                    <thead>
                        <tr>
                            <template v-for="(column, key) in columns">
                                <th :key="'header-' + key" :class="getHeaderClass(column)" v-if="key != 'row:class'">{{ column.label }}</th>
                            </template>
                        </tr>
                    </thead>
                    <draggable v-model="data" tag="tbody" @change="reorder" v-bind="dragOptions" :no-transition-on-drag="false">
                        <tr v-for="(row, id) in data" :key="'row-' + id" :class="row['row:class']">
                            <template v-for="(column, key) in columns">
                                <td :key="'column-' + key" :class="getColumnClass(column)" v-if="key != 'row:class'">
                                    <template v-if="key == 'table-actions'">
                                        <div class="btn-group" role="group" data-toggle="buttons">
                                            <button class="btn btn-sm btn-alt-secondary" type="button" data-toggle="click-ripple"
                                                v-for="(action, key) in row.actions"
                                                :key="'button' + key"
                                                :disabled="action.actionUri ? false : true"
                                                v-on:click="tableAction(action, $event)"
                                                :title="action.label"
                                            ><i v-if="action.icon" :class="action.icon"></i></button>
                                        </div>
                                    </template><template v-else>
                                        <a v-if="isAction(row[key])" v-on:click="tableAction(row[key], $event)" class="link-fx" href="javascript:void(0);" v-html="row[key].label"></a>
                                        <a v-else-if="isImage(row[key])">
                                            <img :src="row[key].image" :width="getImageSize(row[key],'width')" :height="getImageSize(row[key],'height')" :alt="row[key].alt ? row[key].alt : '' ">
                                        </a>
                                        <template v-else-if="isBadge(row[key])"><span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill" :class="row[key].class" style="font-size: 90%;">{{ row[key].label }}</span></template>
                                        <template v-else><div v-html="row[key]"></div></template>
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </draggable>
                </table>
            </div>
            <div class="flex" v-if="pagination">
                <nav>
                    <qc-pagination
                        v-model="page"
                        :per-page="pagination['per-page']"
                        :records="pagination.count"
                        @paginate="pageCallback"
                    ></qc-pagination>
                </nav>
            </div>
        </qc-block>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
