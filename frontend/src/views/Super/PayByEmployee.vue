<template>
  <card>
    <div class="mt-2">
      <validation-observer v-slot="{ handleSubmit }" ref="formValidator">
        <form class="" @submit.prevent="handleSubmit(paymentClicked)">
          <div class="row">
            <div class="col-md-12">
              <label class="form-control-label"
                >Billing Address <span class="requireField">*</span></label
              >
              <base-input
                type="text"
                name="address"
                placeholder="Address"
                rules="required"
                v-model="address"
              >
              </base-input>
            </div>
            <div class="col-md-4">
              <label class="form-control-label"
                >City <span class="requireField">*</span></label
              >
              <base-input
                type="text"
                name="city"
                placeholder="City"
                rules="required"
                v-model="city"
              >
              </base-input>
            </div>
            <div class="col-md-4">
              <label class="form-control-label"
                >State <span class="requireField">*</span></label
              >
              <base-input
                type="text"
                name="state"
                placeholder="State"
                rules="required"
                v-model="state"
              >
              </base-input>
            </div>
            <div class="col-md-4">
              <label class="form-control-label"
                >Zipcode <span class="requireField">*</span></label
              >
              <base-input
                type="text"
                name="zipcode"
                placeholder="Zipcode"
                rules="required"
                v-model="zipcode"
              >
              </base-input>
            </div>
          </div>
          <div class="row">
            <div class="col-12 mb-2">
              <b style="color:#13569a">Actual Amount:</b>
              {{ formatPrice(orignalAmount) }}
            </div>
            <div class="col-12 mb-2">
              <b style="color:#13569a">Amount Payable: </b>

              <span
                >{{ formatPrice(amountPayable) }}
                <span>({{ discount }}% Off)</span></span
              >
            </div>
          </div>

          <!-- <base-input
            prepend-icon="ni ni-credit-card"
            class="mb-3"
            placeholder="Card number"
            v-model="card.cardNumber"
          >
          </base-input>

          <div class="row">
            <div class="col-md-6 col-8">
              <base-input
                prepend-icon="ni ni-calendar-grid-58"
                class="mb-3"
                placeholder="MM/YY"
                v-model="card.expire"
              >
              </base-input>
            </div>
          </div> -->
           <!-- Stripe Element   -->
            <stripe-element-card
            ref="elementRef"
            :pk="publicKey"
            :hidePostalCode="postalCode"
            @token="tokenCreated"
          />
        <!-- End Stripe Element   -->
          <div class="text-right">
            <base-button class="custom-btn" native-type="submit"
              >Pay</base-button
            >
          </div>
        </form>
      </validation-observer>
    </div>
    <div class="row mt-4 justify-content-between align-items-right">
      <div class="col text-right">
        <img
          width="100px"
          src="img/icons/cards/pci-dss-logo.png"
          alt="Image placeholder"
        />
      </div>
    </div>
  </card>
</template>
<script>
import { StripeElementCard } from '@vue-stripe/vue-stripe';
export default {
  name: "pay-by-employee",
components: {
    StripeElementCard,
  },
  props: {
    type: String,
    amountPayable: Number,
    orignalAmount: Number,
    discount: String,
    address: String,
    city: String,
    state: String,
    zipcode: String
  },
  data() {
    return {
      Address: {
        address: "",
        city: "",
        state: "",
        zipcode: ""
      },
      card: {
        cardNumber: "",
        expire: "",
        token: null,
      },
       publicKey: process.env.VUE_APP_STRIPE_PUBLIC_KEY,
      postalCode: true,
    };
  },
  created() {},
  methods: {
    formatPrice(value) {
      return (
        "$ " + value.toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,")
      );
    },
    tokenCreated (token) {
      this.card.token=token;
       this.Address.zipcode = this.zipcode;
      this.Address.address = this.address;
      this.Address.state = this.state;
      this.Address.city = this.city;
      this.$emit("payClicked", this.card, this.Address);
     // this.$emit("payClicked", this.card);
      // handle the token
      // send it to your server
    },
    paymentClicked() {
        this.$refs.elementRef.submit();
    },
  }
};
</script>
<style scoped>
.bg-gradient-primary {
  background: linear-gradient(87deg, #07c9fb 0, #ffffff 100%) !important;
}
</style>
