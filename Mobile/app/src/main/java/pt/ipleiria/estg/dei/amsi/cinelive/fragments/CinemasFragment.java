package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import static android.content.Context.MODE_PRIVATE;

import android.content.Context;
import android.content.SharedPreferences;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.adapters.CinemasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentCinemasBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link CinemasFragment#newInstance} factory method to
 * create an instance of this fragment.
 */
public class CinemasFragment extends Fragment {
    private FragmentCinemasBinding binding;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentCinemasBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        List<Cinema> listaFake = Arrays.asList(
                new Cinema(1, "Cinema Leiria", "Rua das Flores N5, Leiria", 123456789, "leiria@cinelive.pt", "10:00 - 23:00", "12 Salas • 800 Lugares"),
                new Cinema(2, "Cinema Coimbra", "Av. Fernão Magalhães, Coimbra", 123456789, "coimbra@cinelive.pt", "10:00 - 23:00", "12 Salas • 800 Lugares"),
                new Cinema(3, "Cinema Lisboa", "Rua Augusta 115, Lisboa", 123456789, "lisboa@cinelive.pt", "10:00 - 23:00", "12 Salas • 800 Lugares")
        );

        binding.rvCinemas.setLayoutManager(new LinearLayoutManager(getContext()));

        SharedPreferences prefs = requireContext().getSharedPreferences("UserPreferences", Context.MODE_PRIVATE);
        int cinemaSelecionado = prefs.getInt("cinema_id", -1);

        // Cria o adapter
        CinemasAdapter adapter = new CinemasAdapter(listaFake, cinemaSelecionado, cinemaId -> {
            // Guardar nas SharedPreferences
            prefs.edit().putInt("cinema_id", cinemaId).apply();
        });

        binding.rvCinemas.setAdapter(adapter);
    }


    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}