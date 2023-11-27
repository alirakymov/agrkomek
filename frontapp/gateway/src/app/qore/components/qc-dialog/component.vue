<template>
    <transition
        name="modal"
    >
        <div v-if="dialogShow" class="modal-mask scroll-dependency">
            <div class="modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: block;">
                <div class="modal-dialog modal-dialog-popin" role="document">
                    <div class="modal-content">
                        <div class="block block-rounded block-transparent mb-0">
                            <div class="block-content p-0">
                                <slot></slot>
                                <component v-for="component in components"
                                    :key="component.id"
                                    :is="component.type"
                                    :options="component.data"
                                ></component>
                            </div>
                            <div v-if="dialogActions.length" class="block-content block-content-full text-end bg-body">
                                <button v-for="action in dialogActions" @click="evalAction(action.action)"
                                    type="button" class="btn btn-sm me-2" :class="getActionType(action)">
                                    {{ action.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
