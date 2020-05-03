// 从china-area-data 库中加载数据
const addressData = require('china-area-data/v4/data');

// 引入lodash, lodash是一个使用工具库, 提供很多常用的方法
import _ from 'lodash';

// 注册select-town的vue组件
Vue.component('select-town', {
        // 定义组件的属性
        props: {
            // 初始化市镇的值
            initValue: {
                type: Array, // 格式是数组
                default: () => ([]), // 默认空数组
            }
        },

        // 定义这个组件内的数据
        data() {
            return {
                regions: addressData['86'], // 大区列表
                provinces: {}, // 省份列表
                towns: {}, // comune列表
                regionId: '', // 当前选中的大区
                provinceId: '', // 当前选中的省份
                townId: '', // 当前选中的市镇
            };
        },
        // 定义观察器, 对应属性变更时会触发对应的观察器函数
        watch: {
            // 当选择的大区发生变化时触发
            regionId(newVal) {
                if (!newVal) {
                    this.provinces = {};
                    this.provinceId = '';
                    return;
                }

                // 将省份列表设为当前大区下的城市
                this.provinces = addressData[newVal];

                // 如果当前选中的省份不在当前大区下, 则将当前选中省份清空
                if (!this.provinces[this.provinceId]) {
                    this.provinceId = '';
                }
            },

            // 当选择的省份发生变化
            provinceId(newVal) {
                if (!newVal) {
                    this.towns = {};
                    this.townId = '';
                    return;
                }

                // 将市镇列表设为当前省份下的市镇
                this.towns = addressData[newVal];
                // 如果当前选中的市镇不在当前省份下, 则将选中市镇清空
                if (!this.towns[this.townId]) {
                    this.townId = '';
                }
            },

            // 当选择的市镇发生变化时触发
            townId() {
                // 触发一个名为change的Vue事件, 事件的值就是当前选中的大区省份市镇名称, 格式为数组
                this.$emit('change', [this.regions[this.regionId], this.provinces[this.provinceId], this.towns[this.townId]]);
            },
        },
        // 组件初始化时调用
        created() {
            this.setFromValue(this.initValue);
        },
        methods: {
            setFromValue(value) {
                // 过滤空值
                value = _.filter(value);
                // 如果数组长度为0, 则将大区清空
                if (value.length == 0) {
                    this.regionId = '';
                    return;
                }
                // 从当前大区列表中找到与数组第一个元素同名的项的索引
                const regionId = _.findKey(this.regions, o => o === value[0]);
                // 没找到,清空大区的值
                if (!regionId) {
                    this.regionId = '';
                    return;
                }

                // 找到了, 将当前省设置成对应的ID
                this.regionId = regionId;

                // 由于观察器的作用, 这个时候省份列表已经变成了对应大区的省份列表
                // 从当前省份列表找到与数组第二个元素同名的项的索引
                const provinceId = _.findKey(addressData[regionId], o => o === value[1]);

                // 没找到
                if (!provinceId) {
                    this.provinceId = '';
                    return;
                }

                // 找到了, 将当前省设置对应的id
                this.provinceId = provinceId;

                // 由于观察器的作用, 这是时候市镇列表已经编程对应省的市镇列表
                // 从当前市镇列表找到与数组第三个元素同名的项的索引
                const townId = _.findKey(addressData[provinceId], o => o === value[2]);
                // 没有找到
                if (!townId) {
                    this.townId = '';
                    return;
                }
                // 找到了, 将当前市镇设置成对应的id
                this.townId = townId;
            }
        }
    }
);