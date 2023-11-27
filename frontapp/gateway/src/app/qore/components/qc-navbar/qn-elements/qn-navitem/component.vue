<template>
    <li class="nav-main-item">
        <a aria-haspopup="true" aria-expanded="false" class="nav-main-link"
            :href="route"
            :class="{'nav-main-link-submenu' : sublevel}"
            :data-toggle="sublevel ? 'submenu' : null"
        >
            <i class="nav-main-link-icon" :class="icon"></i><span class="nav-main-link-name">{{ itemData.label }}</span>
        </a>
        <ul v-if="sublevel" class="nav-main-submenu">
            <component v-for="item in subItems"
                :key="item.id"
                :is="item.type"
                :item-data="item.data"
            ></component>
        </ul>
    </li>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
