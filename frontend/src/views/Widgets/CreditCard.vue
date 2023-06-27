<template>
  <card>
    <div class="mt-2">
      <form role="form" class="">
        <div class="row">
          <div class="col-4" v-if="type == 'company' && !specialCourseFlag">
            <input
              type="radio"
              name="paymenttype"
              value="monthly"
              v-model="card.paymentType"
            />
            Monthly
          </div>
          <div class="col-4 " v-if="type == 'company' && !specialCourseFlag">
            <input
              type="radio"
              value="yearly"
              name="paymenttype"
              v-model="card.paymentType"
            />
            Yearly
          </div>
          <div class="col-12 mb-2">
            Amount Payable:
            <span
              v-if="
                card.paymentType == 'monthly' &&
                  monthlyAmount &&
                  !specialCourseFlag
              "
              >{{ formatPrice(monthlyAmount) }}</span
            >
            <span v-if="card.paymentType == 'yearly' && yearlyAmount"
              >{{ formatPrice(yearlyAmount) }}
              <span v-if="!specialCourseFlag">(10% Off)</span></span
            >
          </div>
        </div>
<!-- 
        <base-input
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
          <span>
            <!-- Stripe Element   -->
              <stripe-element-card
              ref="elementRef"
              :pk="publicKey"
              :hidePostalCode="postalCode"
              @token="tokenCreated"
            />
            <!-- End Stripe Element   -->
        </span>
        <base-input
          type="text"
          name="Address"
          label="Billing Address *"
          placeholder="Address"
          rules="required"
          v-model="address"
        >
        </base-input>
        <div class="row">
          <div class="col-md-5 col-8">
            <base-input
              type="text"
              name="City"
              label="City *"
              placeholder="City"
              rules="required"
              v-model="city"
            >
            </base-input>
          </div>

          <div class="col-md-3 col-8">
            <base-input
              type="text"
              name="State"
              label="State *"
              placeholder="State"
              rules="required"
              v-model="state"
            >
            </base-input>
          </div>

          <div class="col-md-4 col-8">
            <base-input
              type="number"
              label="Zip Code *"
              name="Zip code"
              placeholder="Zip"
              rules="required"
              v-model="zip"
            >
            </base-input>
          </div>
        </div>
        <base-button class="custom-btn" @click.prevent="paymentClicked" block :disabled="enablePaymentButton"
          >Pay & Create Account</base-button
        >
      </form>
    </div>
    <div class="row mt-4 justify-content-between align-items-left">
      <div class="col-md-6 text-left">
        <img
          width="100px"
          src="img/icons/cards/pci-dss-logo.png"
          alt="Image placeholder"
        />
      </div>
      <div class="col-md-6 text-right">
        <img
          width="150px"
          src="img/icons/cards/credit-card.jpg"
          alt="Image placeholder"
        />
      </div>
    </div>
  </card>
</template>
<script>
import {StripeElementCard} from '@vue-stripe/vue-stripe';
//Vue.use(VueCardFormat);
export default {
    name: "master-card",
    //props: ["monthlyAmount",],
    components: {
        StripeElementCard,
    },
    props: {
        type: String,
        address: String,
        state: String,
        city: String,
        zip: String,
        monthlyAmount: Number,
        yearlyAmount: Number,
        specialCourseFlag: Number,
        enablePaymentButton: Boolean,
    },
    data() {
        return {
            card: {
                cardNumber: "",
                expire: "",
                paymentType: "monthly",
                address: this.address,
                state: this.state,
                city: this.city,
                zip: this.zip,
                token: null,
            },
            publicKey: process.env.VUE_APP_STRIPE_PUBLIC_KEY,
            postalCode: true,
        };
    },
    created() {
        if (this.type == "company") {
            if (this.specialCourseFlag) {
                this.card.paymentType = "yearly";
            } else {
                this.card.paymentType = "monthly";
            }
        }
    },
    methods: {
        formatPrice(value) {
            return (
                "$ " + value.toFixed(2).replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,")
            );
        },
        tokenCreated(token) {
            this.card.token = token;
            this.card.zip = this.zip;
            this.card.address = this.address;
            this.card.state = this.state;
            this.card.city = this.city;
            this.$emit("payClicked", this.card);
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
