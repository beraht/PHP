<?php
/**
     * 检查课程表安排是否存在交叉数据
     * @param  int    $curriculum_id        课程ID
     * @param  int    $curriculum_begintime 具体上课开始时间
     * @param  int    $curriculum_endime    具体上课结束时间
     * @return true / string
     */
    private function checkCurriculumCross(int $curriculum_id, int $curriculum_begintime, int $curriculum_endime, int $timetable_id = 0)
    {
        $other_map = [];
        if ($timetable_id) {
            $other_map['timetable_id'] = ['neq', $timetable_id];
        }
        $where_sql = "(curriculum_begintime <= :begin AND curriculum_endime >= :begin) OR (curriculum_begintime <= :end AND curriculum_endime >= :end) OR (curriculum_begintime >= :begin AND curriculum_endime <= :end)";
        $where_sql = str_replace([':begin', ':end'], [$curriculum_begintime, $curriculum_endime], $where_sql);
        $curriculum_cross = \app\admin\model\Curriculumtimetable::field('timetable_id')
                                                        ->where('curriculum_id', $curriculum_id)
                                                        ->where($where_sql)
                                                        ->where($other_map)
                                                        ->find();
        return $curriculum_cross ? '数据库中存在交叉的课程具体上课安排' : true;
    }