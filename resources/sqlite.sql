-- #! sqlite
-- #{ pvpcounter
-- #    { init
CREATE TABLE IF NOT EXISTS Data (
    Player VARCHAR(40) NOT NULL,
    Cps BOOL DEFAULT TRUE,
    Reach BOOL DEFAULT TRUE,
    Combo BOOL DEFAULT TRUE
);
-- #    }
-- #    { register
-- #        :player string
-- #        :cps int
-- #        :reach int
-- #        :combo int
INSERT OR REPLACE INTO Data(Player, Cps, Reach, Combo)
VALUES (:player, :cps, :reach, :combo);
-- #    }
-- #    { get
-- #        :player string
SELECT
    Cps,
    Reach,
    Combo
FROM Data WHERE Player = :player;
-- #    }
-- #    { remove
-- #        :player string
DELETE FROM Data WHERE Player = :player;
-- #    }
-- #    { update
-- #        { cps
-- #            :player string
-- #            :value int
UPDATE Data SET Cps = :value WHERE Player = :player;
-- #        }
-- #        { reach
-- #            :player string
-- #            :value int
UPDATE Data SET Reach = :value WHERE Player = :player;
-- #        }
-- #        { combo
-- #            :player string
-- #            :value int
UPDATE Data SET Combo = :value WHERE Player = :player;
-- #        }
-- #    }
-- #}