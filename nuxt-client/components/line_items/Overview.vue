<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Repair Order Number:</span>
            <span>{{ line_item.repair_order.ro_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Repair Order:</span>
            <span>{{ line_item.repair_order.desc }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Line Item Category:</span>
            <span>{{ line_item.line_item_category.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">price:</span>
            <span>{{ line_item.price | formatMoney }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Quantity:</span>
            <span>{{ line_item.quantity | formatMoneyNoSign }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Total Price:</span>
            <span>{{ line_item.total_price | formatMoney }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "LineItemOverview",
  props: ['value', 'line_item'],
  data: () => ({
    working: false,
  }),
  async created () {
    if (!this.line_item.line_item_category) {
      this.line_item.line_item_category = {
        desc: null
      }
    }
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
