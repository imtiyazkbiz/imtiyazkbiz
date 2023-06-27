<template>
    <div>
        <ul>
            <li v-for="(company, index) in companies" :key="company.id" @click="getChildren(company)">
                {{ company.name }}
                <pre>
                    {{ company.childrens }}
                </pre>
                <test v-if="company.childrens" :companies="company.chidrens"></test>
            </li>
        </ul>
    </div>

</template>
<script>
import Test from '@/components/Test.vue';

export default {
    name: 'Test',
    components: {
        Test,
    },
    props: [
        'companies'
    ],
    methods: {
        getChildren: function (company) {
            this.$emit('get-child-company', company.id);
        }
    }
}
</script>