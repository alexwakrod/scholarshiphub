/**
 * Provides haptic vibration feedback on mobile devices (Android only) for
 * confirmations, errors, and long‑press actions as specified in D‑48.
 */
export function useTactileFeedback() {
  function vibrate(pattern) {
    try {
      if (window.navigator && window.navigator.vibrate) {
        window.navigator.vibrate(pattern);
      }
    } catch (e) {
      // Vibration not supported
    }
  }

  /**
   * Short tap feedback (10 ms).
   */
  function tapFeedback() {
    vibrate(10);
  }

  /**
   * Confirmation success (two short pulses).
   */
  function successFeedback() {
    vibrate([15, 30, 15]);
  }

  /**
   * Error warning (long single vibration).
   */
  function errorFeedback() {
    vibrate(50);
  }

  /**
   * Long‑press feedback (heavy).
   */
  function longPressFeedback() {
    vibrate([30, 40, 30]);
  }

  /**
   * Drag / drop start feedback.
   */
  function dragStartFeedback() {
    vibrate(20);
  }

  return {
    tapFeedback,
    successFeedback,
    errorFeedback,
    longPressFeedback,
    dragStartFeedback,
  };
}