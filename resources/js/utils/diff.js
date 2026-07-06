/**
 * Simple line‑based diff returning arrays of chunks with `type` and `value`.
 * Chunk types: 'equal', 'added', 'removed'
 */
export function computeDiff(oldText, newText) {
  const oldLines = oldText.split('\n');
  const newLines = newText.split('\n');
  const result = [];
  let oldIdx = 0;
  let newIdx = 0;

  while (oldIdx < oldLines.length || newIdx < newLines.length) {
    if (oldIdx < oldLines.length && newIdx < newLines.length && oldLines[oldIdx] === newLines[newIdx]) {
      result.push({ type: 'equal', value: oldLines[oldIdx] });
      oldIdx++;
      newIdx++;
    } else {
      // look ahead to find next match
      let matchOld = -1;
      let matchNew = -1;
      for (let i = oldIdx; i < oldLines.length; i++) {
        for (let j = newIdx; j < newLines.length; j++) {
          if (oldLines[i] === newLines[j]) {
            matchOld = i;
            matchNew = j;
            break;
          }
        }
        if (matchOld !== -1) break;
      }

      if (matchOld === -1) {
        // no more matches; remaining lines are removed or added
        while (oldIdx < oldLines.length) {
          result.push({ type: 'removed', value: oldLines[oldIdx] });
          oldIdx++;
        }
        while (newIdx < newLines.length) {
          result.push({ type: 'added', value: newLines[newIdx] });
          newIdx++;
        }
        break;
      } else {
        // remove lines in old up to matchOld
        while (oldIdx < matchOld) {
          result.push({ type: 'removed', value: oldLines[oldIdx] });
          oldIdx++;
        }
        // add lines in new up to matchNew
        while (newIdx < matchNew) {
          result.push({ type: 'added', value: newLines[newIdx] });
          newIdx++;
        }
      }
    }
  }

  return result;
}