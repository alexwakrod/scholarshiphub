import Dexie from 'dexie';
import { ref } from 'vue';

const db = new Dexie('ScholarshipHubOffline');
db.version(1).stores({
  scholarships: 'id, title, provider, country, deadline, status, amount',
  profile: 'key',
  bookmarks: 'scholarship_id',
  applications: 'id',
});

const isOnline = ref(navigator.onLine);

window.addEventListener('online', () => {
  isOnline.value = true;
  syncPendingActions();
});

window.addEventListener('offline', () => {
  isOnline.value = false;
});

const pendingActions = [];

export function useOfflineSync() {
  async function cacheScholarships(scholarships) {
    try {
      if (!scholarships || !scholarships.length) return;
      await db.scholarships.bulkPut(scholarships);
    } catch (e) {
      console.error('[OfflineSync] cacheScholarships error:', e);
    }
  }

  async function getCachedScholarships() {
    try {
      return await db.scholarships.toArray();
    } catch (e) {
      console.error('[OfflineSync] getCachedScholarships error:', e);
      return [];
    }
  }

  async function cacheProfile(profileData) {
    try {
      await db.profile.put({ key: 'profile', data: profileData });
    } catch (e) {
      console.error('[OfflineSync] cacheProfile error:', e);
    }
  }

  async function getCachedProfile() {
    try {
      const entry = await db.profile.get('profile');
      return entry?.data || null;
    } catch (e) {
      console.error('[OfflineSync] getCachedProfile error:', e);
      return null;
    }
  }

  async function cacheBookmarks(bookmarkIds) {
    try {
      await db.bookmarks.clear();
      const rows = bookmarkIds.map(id => ({ scholarship_id: id }));
      await db.bookmarks.bulkPut(rows);
    } catch (e) {
      console.error('[OfflineSync] cacheBookmarks error:', e);
    }
  }

  async function getCachedBookmarks() {
    try {
      const rows = await db.bookmarks.toArray();
      return rows.map(r => r.scholarship_id);
    } catch (e) {
      console.error('[OfflineSync] getCachedBookmarks error:', e);
      return [];
    }
  }

  function queueAction(action) {
    pendingActions.push(action);
  }

  async function syncPendingActions() {
    if (!isOnline.value) return;
    while (pendingActions.length > 0) {
      const action = pendingActions.shift();
      try {
        await action();
      } catch (e) {
        console.error('[OfflineSync] action failed, re-queuing:', e);
        pendingActions.unshift(action);
        break;
      }
    }
  }

  return {
    isOnline,
    cacheScholarships,
    getCachedScholarships,
    cacheProfile,
    getCachedProfile,
    cacheBookmarks,
    getCachedBookmarks,
    queueAction,
    syncPendingActions,
  };
}