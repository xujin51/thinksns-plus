<style lang="scss">
.setting-container {
  // padding-top: 70px;
  .setting-nav {
    padding: 12px 12px 0;
  }
}
</style>

<template>
  <div class="setting-container">
    <!-- The settings nav. -->
    <ul class="nav nav-tabs setting-nav">
      <router-link to="/setting/base" tag="li" active-class="active">
        <a href="#">基本信息</a>
      </router-link>
      <router-link to="/setting/area" tag="li" active-class="active">
        <a href="#">地区库</a>
      </router-link>
      <router-link to="/setting/app/theme" tag="li" active-class="active">
        <a href="#">APP主题</a>
      </router-link>
      <router-link to="/setting/filter" tag="li" active-class="active">
        <a href="#">过滤词库</a>
      </router-link>
      <router-link to="/setting/storeages" tag="li" active-class="active">
        <a href="#">储存管理</a>
      </router-link>
    </ul>
    <!-- End settings nav. -->

    <!-- The setting container view -->
    <router-view></router-view>
    <!-- End -->
  </div>
</template>
