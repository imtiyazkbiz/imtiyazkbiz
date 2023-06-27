<template>
    <div class="content">
        <base-header class="pb-6">
            <div class="row align-items-center py-2"></div>
        </base-header>
        <div class="container-fluid mt--6">
            <card class="no-border-card" footer-classes="pb-2">
                <test :companies="companies" @get-child-company="getChildCompany"></test>
            </card>
        </div>
    </div>
</template>
<script>
import Test from '@/components/Test.vue'

export default {
    components: {
        Test,
    },
    data() {
        return {
            companies: [],
        };
    },
    created: function () {
        this.getParentCompanies();
    },
    methods: {
        getParentCompanies: function () {
            this.$http.get('testing-debugging/get-parent-companies').then((response) => {
                this.companies = response.data;
            });
        },
        getChildCompany: function (company) {
            this.companies[0]['childrens'] = [];
            this.companies[0]['childrens'].push(this.companies[1]);
            this.companies[0]['childrens'].push(this.companies[2]);
            console.log(this.companies);
            /*this.$http.get('testing-debugging/get-child-companies', {
                params: {
                    id: company,
                }
            }).then((response) => {
                this.companies = response.data;
                this.companies[0]['childrens'] = this.companies[1];
            });*/
        }
    }
};
</script>
<style>
.no-border-card .card-footer {
    border-top: 0;
}
</style>
