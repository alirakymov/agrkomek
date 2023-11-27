<template>
    <div class="w-100">
        <qc-block :options="{name: name + '-block', title: title, actions: actions}" @cdestroy="cdestroy()">
            <ul class="nav nav-tabs nav-tabs-alt mx-n125" role="tablist">
                <li class="nav-item" v-for="tab in preparedTabs">
                    <a class="nav-link" :class="{'active' : tab.active}" :href="'#' + tab.id" @click="tab.active = true" data-bs-toggle="tab">{{ tab.label }}</a>
                </li>
            </ul>
            <div class="tab-content mx-n125 mb-n125">
                <div :class="'tab-pane ' + (tab.active ? 'active in' : '')" :id="tab.id" v-for="tab in preparedTabs">
                    <component :is="component.type" :key="component.id" :options="component.data" v-for="component in getTabComponents(tab.components)"></component>
                </div>
            </div>
        </qc-block>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
