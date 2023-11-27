<template>
    <div :id="name" :class="isBlock ? 'block block-rounded block-fx-pop' : 'w-100'">
        <div class="block-header" :class="isBlock ? 'p-3' : 'p-0 pb-3'" >
            <h3 class="block-title">{{ title }}</h3>
            <div class="block-options">
                <a class="btn btn-sm btn-alt-secondary px-25 me-1"
                    v-for="action in computedActions" @click="processAction(action)"
                ><i :class="pickIcon(action)"></i></a>
            </div>
        </div>
        <div class="pt-0" :class="{ 'block-content': indents}">
            <slot></slot>
            <component v-for="component in components"
                :key="component.id"
                :is="component.type"
                :options="component.data"
            ></component>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.scss"></style>
