<template>
    <div>
        <div :id="uploadFormID" class="dropzone mb-125 machinery-dropzone">
            <draggable class="row g-sm items-push js-gallery push js-gallery-enabled"
                v-model="images"
                :no-transition-on-drag="false"
                tag="div" >
                <div class="col-md-4 col-lg-3 col-xl-2 me-3 mt-3 animated fadeIn"
                     v-for="(image, key) in images"
                >
                    <div class="options-container rounded-3">
                        <img class="img-fluid options-item" :src="image" alt="">
                        <div class="options-overlay bg-black-75">
                            <div class="options-overlay-content">
                                <div class="btn-group" role="group" data-toggle="buttons">
                                    <a class="btn btn-sm btn-warning" @click="deleteImage(key)"><i class="far fa-trash-alt"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </draggable>
        </div>

        <div class="form-floating mb-4">
            <input type="text" class="form-control"
                placeholder="Название"
                v-model="machinery.title"
            >
            <label>Название</label>
            <div class="form-text animated fadeInUp">название техники</div>
        </div>
        <div class="form-floating mb-4">
            <input type="text" class="form-control"
                placeholder="Цена"
                v-model="machinery.price"
            >
            <label>Цена</label>
            <div class="form-text animated fadeInUp">введите стоимость техники</div>
        </div>
        <div class="form-floating mb-4" v-for="(param, key) in params">
            <input type="text" class="form-control"
                placeholder="Новый параметр"
                v-model="params[key]"
            >
            <label>Параметр</label>
            <div class="form-text animated fadeInUp">дополнительный параметр</div>
        </div>
        <div class="mb-4"><a href="javascript:void(0);" @click="addParam()">+ добавить параметр</a></div>
        <div class="form-floating mb-4">
            <textarea class="form-control" style="height: 120px"
                placeholder="Описание"
                v-model="machinery.content"
                rows="10"
            ></textarea>
            <label>Описание</label>
            <div class="form-text animated fadeInUp">опишите технику</div>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-alt-primary" @click="save()">Сохранить</button>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.scss"></style>
