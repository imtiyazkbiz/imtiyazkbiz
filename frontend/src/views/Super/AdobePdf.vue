<template>
    <div id="adobe-dc-view" style="height: 560px; width: 100%" v-loading="loading"></div>
</template>
<script>
export default {
    name: "adobe-pdf",
    props: {
        path: String,
        url: String
    },
    data() {
        return {
            loading: false
        };
    },
    mounted() {
        console.clear();
        console.log(process.env.VUE_APP_API_URL + "/" + this.path + "/" + this.url);
        this.loading = true;
        this.$loadScript("https://documentservices.adobe.com/view-sdk/viewer.js").then(
            () => {
                document.addEventListener(
                    "adobe_dc_view_sdk.ready",
                    this.getPdf(this.url, this.path)
                );
            }
        );
    },
    methods: {
        getPdf(file, path) {
            setTimeout(function() {
                let adobeDCView = new AdobeDC.View({
                    clientId: process.env.VUE_APP_ADOBE_PDF_CLIENT_ID,
                    divId: "adobe-dc-view"
                });
                let pdfEmbedOptions = {
                    embedMode: "SIZED_CONTAINER",
                    showAnnotationTools: false,
                    showDownloadPDF: false,
                    showPrintPDF: false
                    //showLeftHandPanel: false,
                    //dockPageControls: false
                };
                this.loading = false;
                adobeDCView.previewFile(
                    {
                        content: {
                            location: {
                                url: process.env.VUE_APP_API_URL + "/" + path + "/" + file
                            }
                        },
                        metaData: { fileName: file }
                    },
                    pdfEmbedOptions
                );
            }, 2000);
        }
    }
};
</script>
<style>
@media only screen and (max-width: 760px),
(min-device-width: 768px) and (max-device-width: 1024px) {
    #adobe-dc-view{
        height: 100% !important;
    }
}

</style>
