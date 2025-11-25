package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.ArrayAdapter;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivitySelecionarCinemaBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;

public class SelecionarCinemaActivity extends AppCompatActivity {

    ActivitySelecionarCinemaBinding binding;
    private PreferencesManager preferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Lista de cinemas
        List<Cinema> cinemas = Arrays.asList(
                new Cinema(1, "Cinema Leiria"),
                new Cinema(2, "Cinema Coimbra"),
                new Cinema(3, "Cinema Lisboa")
        );

        // Verificar que o cinema existe e é válido
        boolean cinemaExists = false;
        for (Cinema cinema: cinemas) {
            if (cinema.getId() == preferences.getCinemaId()) {
                cinemaExists = true;
                break;
            }
        }

        // Se já tiver cinema definido --> MainActivity
        if (cinemaExists) {
            startActivity(new Intent(this, MainActivity.class));
            finish();
            return;
        }

        binding = ActivitySelecionarCinemaBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        ArrayAdapter<Cinema> adapter = new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, cinemas);
        binding.lvCinemas.setAdapter(adapter);

        // Clique num item da lista
        binding.lvCinemas.setOnItemClickListener((parent, view, position, id) -> {
            Cinema cinemaSelecionado = cinemas.get(position);

            preferences.setCinemaId(cinemaSelecionado.getId());

            startActivity(new Intent(this, MainActivity.class));
            finish();
        });
    }
}