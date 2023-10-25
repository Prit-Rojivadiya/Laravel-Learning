<template>
  <v-app class="pa-6">
    <Header />
    <Sidebar />
    <v-main class="content">
      <nuxt />
      <Footer />
    </v-main>
  </v-app>
</template>

<style>
  .v-main__wrap {
    padding: 70px 24px 24px;
  }
</style>

<script>
import Header from '@/components/Header'
import Sidebar from '@/components/Sidebar'
import Footer from '@/components/Footer'

export default {
  components: { Footer, Header, Sidebar }
}

</script>

<style lang="scss" scoped>
  .setting-fab {
    top: 50% !important;
    right: 0;
    border-radius: 0;
  }

  .page-wrapper {
    min-height: calc(100vh - 64px - 50px - 81px);
    margin-bottom: 50px;
  }

  .app--footer {
    position: absolute;
    bottom: 0;
    width: 100%;
  }
</style>
