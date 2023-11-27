<template>
    <div class="w-100">
        <ul class="nav nav-tabs nav-tabs-alt" :class="{'mb-3': indents}" role="tablist">
            <li class="nav-item" v-for="component in components">
                <a class="nav-link"
                    :class="{'active' : activeComponent == component.name}"
                    href="javascript:void(0);" @click="activeComponent = component.name"
                >{{ component.data.label }}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active"
                v-for="component in components"
                v-show="activeComponent === component.name"
                :id="component.name"
            >
                <component :is="component.type" :key="component.id" :options="component.data" ref="children"></component>
            </div>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
