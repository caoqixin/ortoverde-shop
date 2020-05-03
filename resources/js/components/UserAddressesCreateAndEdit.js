// 注册user-address-create-and-edit组件
Vue.component('user-address-create-and-edit', {
    // 组件数据
    data() {
        return {
            region: '', // 大区
            province: '', // 省
            town: '', // 市镇
        }
    },

    methods: {
        // 把参数val的值保存到组件数据中
        onTownChanged(val) {

            if (val.length === 3) {
                this.region = val[0];
                this.province = val[1];
                this.town = val[2];
            }
        }
    }
});