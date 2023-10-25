<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Repair Orders Dashboard</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addRepairOrder">Create Repair Order</v-btn>
        </v-col>
      </v-row>
    </div>

    <RepairOrdersGridComponent
      v-if="showRepairOrders"
      :vehicle="vehicle"
      :showAddRepairOrderInGrid="showAddRepairOrderInGrid"
      :showVehicleInGrid = "showVehicleInGrid"
      v-model="showRepairOrders"
    />

    <ManageRepairOrderComponent
      v-if="addingRepairOrder"
      v-model="addingRepairOrder"
      :allowVehicleAssignment = "allowVehicleAssignment"
      :vehicle="vehicle"
      @repair_order-saved="refresh"
    />


  </div>
</template>

<script>

import RepairOrdersGridComponent from '~/components/repair_orders/RepairOrdersGrid'
import ManageRepairOrderComponent from '~/components/repair_orders/ManageRepairOrder'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    RepairOrdersGridComponent,
    ManageRepairOrderComponent
  },
  data: () => ({
    addingRepairOrder: false,
    showRepairOrders: true,
    showAddRepairOrderInGrid: false,
    showVehicleInGrid: true,
    allowVehicleAssignment: true,
    vehicle: null,
  }),
  methods: {
    addRepairOrder () {
      this.addingRepairOrder = true
    },
    refresh () {
      this.showRepairOrders = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showRepairOrders = true
      });
    }
  }
}
</script>
