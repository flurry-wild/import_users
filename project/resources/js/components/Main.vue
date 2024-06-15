<template>
    <Button label="Запустить импорт" @click="startImport"></Button>

    <div class="m-4">
        <p>ID загрузки: <b>{{ this.info.current_upload_id }}</b></p>
        <p>Количество загруженных строк из файла: <b>{{ this.info.last_processed_row }}</b></p>
    </div>

<!--    <div class="progress">
        <div class="progress-bar" :style="{ width: progress + '%' }" ref="progressBar"></div>
    </div>-->
</template>
<script>
import axios from 'axios';

export default {
    name: 'Index',

    data() {
        return {
            progress: 0,
            info: {}
        };
    },
    mounted() {
        this.getInfo();
        this.progress = 50;
    },
    methods: {
        getInfo() {
            axios.get('/api/current-upload/info').then(res => {
                this.info = res.data;
            })
        },
        startImport() {
            axios.get('/api/import').then(res => {
                console.log(res);
            });
        }
    }
}
</script>
<style>
.progress {
    width: 100%;
    height: 20px;
    background-color: #f0f0f0;
}

.progress-bar {
    height: 100%;
    background-color: #007bff;
    transition: width 0.5s; /* Добавляем анимацию перехода */
}
</style>
