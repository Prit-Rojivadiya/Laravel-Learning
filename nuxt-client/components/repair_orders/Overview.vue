<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Vehicle Unit Number:</span>
            <span>{{ repair_order.vehicle.vehicle_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Vendor:</span>
            <span>{{ repair_order.vendor.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Repair Order Status:</span>
            <span>{{ repair_order.repair_order_status.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Needs Approval:</span>
            <span>{{ repair_order.needs_approval | formatYesNo }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Approval Received:</span>
            <span>{{ repair_order.approval_received_date | formatDateMDY }}</span>
          </div>
        </div>
      </v-flex>

      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Repair Order Number:</span>
            <span>{{ repair_order.ro_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Invoice Number:</span>
            <span>{{ repair_order.invoice_number }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Total Price:</span>
            <span>{{ repairOrderTotalPrice | formatMoney }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Start Date:</span>
            <span>{{ repair_order.start_date | formatDateMDY}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Completed Date:</span>
            <span>{{ repair_order.completed_date | formatDateMDY }}</span>
          </div>
        </div>
      </v-flex>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Meter Reading:</span>
            <span>{{ repair_order.meter_reading}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Linked to PMs:</span>
            <div class="flex-item"  v-for="(pm, index) in repair_order.preventive_maintenances">
              <span>{{ pm.name }}</span>
            </div>
          </div>
          <div class="flex-item">
            <span class="grey--text">Invoice:</span>
            <span>
              <v-file-input v-if="!uploadingFile && repair_order.invoices.length == 0" v-model="file" label="Click here to select a file" />
              <v-progress-linear indeterminate height="25" v-if="uploadingFile">
                <template v-slot:default="{ value }">
                  <strong>Uploading...</strong>
                </template>
              </v-progress-linear>
              <p v-for="invoice in repair_order.invoices">
                <a :href="invoice.url" target="_blank">{{ invoice.name }}</a> <a @click="deleteInvoice(invoice)"><v-icon small>mdi-trash-can</v-icon></a>
              </p>
            </span>
          </div>
        </div>
      </v-flex>
    </v-layout>
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Repair Description:</span>
            <span>{{ repair_order.desc }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Additional Notes:</span>
            <span>{{ repair_order.notes }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
    <v-snackbar
      v-model="snackbar"
      color="success"
      timeout="4000"
    >
      {{ snackbarText }}

      <template v-slot:action="{ attrs }">
        <v-btn
          color="pink"
          text
          v-bind="attrs"
          @click="snackbar = false"
        >
          Close
        </v-btn>
      </template>
    </v-snackbar>

    <v-dialog
      v-model="deleteDialog"
      persistent
      max-width="290"
    >
      <v-card>
        <v-card-title class="headline">Are you sure?</v-card-title>
        <v-card-text>Do you really want to delete?</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" text :disabled="deletingLoading" @click="deleteDialog = false; deletingItem = null">Cancel</v-btn>
          <v-btn color="red" :loading="deletingLoading" @click="confirmedDelete()">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: "RepairOrderOverview",
  props: ['value', 'repair_order','repairOrderTotalPriceUpdated'],
  data: () => ({
    working: false,
    repairOrderTotalPrice: null,
    file: null,
    uploadingFile: false,
    snackbar: false,
    snackbarText: null,
    deleteDialog: false,
    deletingItem: null,
    deletingLoading: false
  }),
  async created () {
    if (!this.repair_order.preventive_maintenance) {
      this.repair_order.preventive_maintenance = {
        name: null
      }
    }

    if (this.repairOrderTotalPriceUpdated) {
      this.repairOrderTotalPrice = this.repairOrderTotalPriceUpdated
    } else {
      this.repairOrderTotalPrice = this.repair_order.total_price
    }
  },
  watch: {
    file(newVal) {
      if (newVal) {
        this.uploadFile();
      }
    }
  },
  methods: {
    deleteInvoice (invoice) {
      this.deleteDialog = true
      this.deletingItem = invoice
    },
    async confirmedDelete () {
      this.deletingLoading = true

      await this.$axios.$delete(`uploads/${this.deletingItem.id}`)

      this.deleteDialog = false
      this.deletingItem = null
      this.deletingLoading = false

      // trigger a reload of data
      this.$emit('uploadedfile');
    },
    async uploadFile () {
      this.uploadingFile = true

      const currentObj = this
      const config = {
        headers: {
          'content-type': 'multipart/form-data'
        }
      }

      // form data
      const formData = new FormData()
      formData.append('file', this.file)
      formData.append('repair_order_id', this.repair_order.id)
      formData.append('file_type', 'invoices')

      this.importDialogWorking = true

      // send upload request
      this.$axios.post('/uploads', formData, config)
        .then((response) => {
          console.log(response)

          currentObj.file = null

          this.$emit('uploadedfile');

          this.uploadingFile = false;

          this.snackbar = false
          this.snackbarText = 'Result file has been successfully imported!'
          this.snackbar = true
        })
        .catch(function (error) {
          currentObj.output = error
        })
        .finally(() => {
          this.importDialogWorking = false
          this.importDialog = false
        })
    }
  }
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
