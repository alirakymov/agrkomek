<template>
    <div>
        <div class="form-floating mb-4" >
            <select class="form-select"
                @change="selectModerator($event)"
                v-model="currentModeratorId"
            >
                <option v-for="moderator in moderators" :value="moderator.id" >{{ moderator.firstname }} ({{ moderator.role.title }})</option>
            </select>
            <label>Консультант</label>
        </div>
        <div id="horizontal-navigation-hover-normal" class="d-lg-block mt-lg-0 mb-5">
            <ul class="nav-main nav-main-horizontal nav-main-hover">
                <li class="nav-main-item">
                    <a class="nav-main-link" href="javascript:void(0)" :class="{ active: viewport == 'main' }" @click="viewport='main'">
                        <span class="nav-main-link-name">Основное</span>
                    </a>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link" href="javascript:void(0)" :class="{ active: viewport == 'history' }" @click="viewport='history'">
                        <span class="nav-main-link-name">История</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="p-zero-margins" v-if="viewport == 'main'">
            <div class="message" v-for="message in messages" :class="{'message-out': message.direction == 2}">
                <div class="message-inner">
                    <div class="message-body">
                        <div class="message-content">
                            <div class="message-text">
                                <p v-html="message.message"></p>
                            </div>
                        </div>
                    </div>

                    <div class="message-footer">
                        <span class="fs-sm text-muted">{{ getDate(message) }}</span>
                    </div>
                </div>
            </div>


            <div class="bordered-ck-editor mt-3" v-if="consultancy.closed == 0">
                <ckeditor :editor="wysiwygEditor" :config="demandWysiwygOptions" v-model="message"/>
                <div>
                    <button type="button" class="btn btn-alt-primary mt-3 me-2" @click="sendMessage()">Ответить</button>
                    <button type="button" class="btn btn-alt-warning mt-3" @click="closeConsultancy()">Закрыть консультацию</button>
                </div>
            </div>
        </div>
        <div class="p-zero-margins" v-if="viewport == 'history'">
            <div class="message" v-for="message in otherMessages" :class="{'message-out': message.direction == 2}">
                <div class="message-inner">
                    <div class="message-body">
                        <div class="message-content">
                            <div class="message-text">
                                <p v-html="message.message"></p>
                            </div>
                        </div>
                    </div>

                    <div class="message-footer">
                        <span class="fs-sm text-muted">{{ getDate(message) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
