<template>
  <v-dialog
    :value="value"
    @input="$emit('input')"
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
  >
    <v-card>
      <v-toolbar dark color="primary">
        <v-btn icon dark @click="$emit('input')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title>
          {{ line_item && line_item.id ? 'Edit' : 'New' }} Line Item
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveLineItem()">
            {{ line_item && line_item.id ? 'Save' : 'Create' }} Line Item
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-alert v-if="errors.length" class="error">
        <template v-for="error in errors">
          {{ error.msg }}<br />
        </template>
        Please try again
      </v-alert>

      <v-form ref="form" v-model="formValid">
        <div class="overline px-4 pt-4 pb-2">Details</div>
        <v-card>
          <v-card-title>
            <div class="flex-item tz-form-item" v-if="allowRepairOrderAssignment">
              <v-select
                v-model="form.repair_order_id"
                :rules="[v => !!v || 'Please select the repair this line item belongs to']"
                :items="repair_orders"
                item-value="id"
                item-text="desc"
                label="Repair Order"
                required
                dense
              />
            </div>
            <div v-else-if="repair_order">
              <div class="flex-item">
                <span class="grey--text">Repair Order Number:</span>
                <span>{{ repair_order.ro_number }}</span>
              </div>
              <div class="flex-item">
                <span class="grey--text">Repair Order Description:</span>
                <span>{{ repair_order.desc }}</span>
              </div>
            </div>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-sm justify-space-around fluid class="p-4">
              <v-layout row>
                <v-flex>
                  <div class="flex-item tz-form-item">
                    <v-autocomplete
                      v-model="form.line_item_category_id"
                      :rules="[v => !!v || 'Please select the category for this line item']"
                      :items="line_item_categories"
                      item-value="id"
                      item-text="cat_type_and_name"
                      label="Category"
                      required
                      dense
                      :filter="lineItemCategoryFilter"
                    />
                  </div>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.price"
                        :rules="[v => !!v || 'Please enter the price per quantity']"
                        label="Price"
                        required
                        dense
                      />
                    </div>
                  </div>
                  <div class="flex-item tz-form-item">
                    <v-text-field
                      v-model.number="form.quantity"
                      :rules="[v => !!v || 'Please enter the quantity']"
                      label="Quantity"
                      required
                      dense
                    />
                  </div>
                  <div class="flex-content">
                    <div class="flex-item tz-form-item">
                      <v-text-field
                        v-model="form.total_price"
                        :rules="[v => !!v || 'Please enter the total price']"
                        label="Total Price"
                        required
                        dense
                        readonly
                      />
                    </div>
                  </div>
                </v-flex>
              </v-layout>
            </v-container>
          </v-card-text>
        </v-card>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>

import DatePicker from '~/components/forms/DatePicker'

export default {
  name: "ManageRepairOrder",
  props: ['value', 'line_item', 'repair_order', 'allowRepairOrderAssignment'],
  components: {
    DatePicker,
  },
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    showDates: true,
    repair_orders: [],
    line_item_categories: [],
    errors: [],
    form: {
      repair_order_id: null,
      line_item_category_id: null,
      price: null,
      quantity: null,
      total_price: null,
      desc: null,
      notes: null,
    }
  }),
  async created () {
    //if creating a new line item from a repair order, default repair order assignment
    if (this.repair_order && this.repair_order.id) {
      this.form.repair_order_id = this.repair_order.id
      this.repair_orders = [this.repair_order]
    }

    if (this.allowRepairOrderAssignment) {
      this.repair_orders = [this.repair_order]
    }

    let tLineItemCategories = await this.$axios.$get(`/line_item_categories?paginate=false`)
    if (tLineItemCategories) {
      this.line_item_categories = tLineItemCategories
    }

    if (this.line_item && this.line_item.id) {
      this.form.repair_order_id = this.line_item.repair_order_id
      this.form.line_item_category_id = this.line_item.line_item_category_id
      this.form.price = this.line_item.price
      this.form.quantity = this.line_item.quantity
      this.form.total_price = this.line_item.total_price
      this.form.desc = this.line_item.desc
      this.form.notes = this.line_item.notes
    }
    else {
      this.form.quantity = 1
    }
  },
  watch: {
    'form.price' (newVal, oldVal) {
      if (newVal != null) {
        this.form.total_price = this.form.quantity * this.form.price
      }
    },
    'form.quantity' (newVal, oldVal) {
      if (newVal != null) {
        this.form.total_price = this.form.quantity * this.form.price
      }
    },
  },
  methods: {
    async saveLineItem () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        let savedLineItem
        if (this.line_item && this.line_item.id) {
          savedLineItem = await this.$axios.$put(`/line_items/${this.line_item.id}`, this.form)
        } else {
          savedLineItem = await this.$axios.$post(`/line_items`, this.form)
        }
        this.$emit('line_item-saved', savedLineItem)
        this.$emit('refresh-total-price', savedLineItem)
        this.working = false
        this.$emit('input')
        this.reset()
      } catch (e) {
        console.log("my error", e.message)
        if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push ({msg: e})
        }
        this.working = false
      }
    },
    refresh (field) {
      //doing watch here, instead of in watch() as it doesn't trigger change on vue component
      //TODO: Ask Michael why datepicker needs this?  SSSF worked without it
      this.showDates = false
      this.$nextTick().then(() => {
        // Add the dates components back in
        this.showDates = true
      });
    },
    reset () {
      this.form = {
        desc: null,
      }
      this.$refs.form.resetValidation()
    },
    lineItemCategoryFilter (item, queryText, itemText) {
      let itemName = item.name.toLowerCase()
      let searchText = queryText.toLowerCase()
      return itemName.indexOf(searchText) > -1
    },
  },
}
</script>

<style scoped>
.v-card .flex-item {
  margin-bottom: 5px;
}

.flex-item.tz-form-item {
  max-width: 80%;
}
</style>
